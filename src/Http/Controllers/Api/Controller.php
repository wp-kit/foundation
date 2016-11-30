<?php
	
	namespace WPKit\Http\Controllers\Api;
	
	use WPKit\Core\Controller as AppController;
	use Exception;
	
	class Controller extends AppController {
		
		/**
	     * Model Class Attribute
	     *
	     */
		protected $modelClass = null;
		
		/**
	     * Save an entity handler
	     *
	     * @param  int  $id
	     * @return void
	     */
		public function save( $id = null ) {
			
			try {
				
				wp_nice_json( $this->saveEntity( $id ) );
				
			} catch(Exception $e) {
				
				status_header( 400 );
				
				wp_send_json_error( $e->getMessage() );
				
			}
			
		}
		
		/**
	     * Get an entity handler
	     *
	     * @param  int  $id
	     * @return Model
	     */
		public function get( $id = null ) {
			
			try {
				
				if( $id ) {
					
					wp_nice_json( $this->getEntity( $id ) );
					
				} else {
					
					wp_nice_json( $this->getEntities() );
					
				}
				
			} catch(Exception $e) {
				
				status_header( 400 );
				
				wp_send_json_error( $e->getMessage() );
				
			}
			
		}
		
		/**
	     * Save an entity
	     *
	     * @param  int  $id
	     * @return Model
	     */
		protected function saveEntity( $id = null ) {
			
			$model = $this->getModel();
			
			if( $id ) {
				
				$model = $model->find($id)->first();
				
			}
			
			$model->fill( $this->http->all() )->save();
			
			return $model;
			
		}
		
		/**
	     * Get an entity
	     *
	     * @param  int  $id
	     * @return Model
	     */
		protected function getEntity( $id ) {
			
			return $this->getModel()->find( $id );
			
		}
		
		/**
	     * Get entities
	     *
	     * @return Collection
	     */
		protected function getEntities() {
			
			$model = $this->getModel();
				
			$query = $model->query();	
			
			if( ! empty( $this->http->get('orderby') ) ) {
				
				$query->orderBy( $this->http->get('orderby'), $this->http->get('order') ? $this->http->get('order') : 'DESC' );
				
			}
			
			return $this->whereQuery( $query, $model )
				->offset( $this->http->get('offset') ? $this->http->get('offset') : 0 )
                ->limit( $this->http->get('limit') ? $this->http->get('limit') : 20 )
                ->get();			
		}
		
		/**
	     * Where query
	     *
	     * @return Model Query
	     */
		protected function whereQuery( $query, $model ) {
		
			return $query;
			
		}
		
		/**
	     * Get model instance
	     *
	     * @return Model
	     */
		protected function getModel() {
			
			try {
				
				$model = $this->modelClass ? '\App\Models\\' . $this->modelClass : ( $this->modelClass !== false ? $this->getModelFromController() : false );
			
				if( ! $model ) {
					
					throw new Exception( 'No Model Found' );
					
				} 
				
				return new $model;
				
			} catch(Exception $e) {
				
				status_header( 400 );
				
				wp_send_json_error( $e->getMessage() );
				
			}
			
		}
		
		/**
	     * Get model from controller
	     *
	     * @return string
	     */
		protected function getModelFromController() {
			
			preg_match( '/(?P<model>\w+)Controller/', get_called_class(), $matches );
			
			$class = '\App\Models\\' . $matches['model'];
			
			return class_exists( $class ) ? $class : false;
			
		}
		
	}