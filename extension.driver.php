<?php

	Class Extension_Router extends Extension{

		public function about() {
			return array('name' => 'URL Router',
						 'version' => '0.3',
						 'release-date' => '2010-04-07',
						 'author' => array('name' => 'Robert Philp',
										   'website' => 'http://robertphilp.com',
										   'email' => ''),
							'description'   => 'Allows URL routing for vanity URLs etc'
				 		);
		}

		public function install() {
            $this->_Parent->Database->query("
                    CREATE TABLE IF NOT EXISTS `tbl_router` (
                        `id` int(11) NOT NULL auto_increment,
                        `from` varchar(255) NOT NULL,
                        `to` varchar(255) NOT NULL,
                        PRIMARY KEY (`id`)
                    )
            ");
        }

        public function uninstall() {
            $this->_Parent->Database->query("DROP TABLE `tbl_router`");
        }

		
		public function getSubscribedDelegates() {
			return array(
				array(
					'page'		=> '/frontend/',
					'delegate'	=> 'FrontendPrePageResolve',
					'callback'	=> 'frontendPrePageResolve'
				),
				array(
					'page'		=> '/system/preferences/',
					'delegate'	=> 'AddCustomPreferenceFieldsets',
					'callback'	=> 'addCustomPreferenceFieldsets'
				),
				array(
					'page'      => '/system/preferences/',
					'delegate'  => 'Save',
					'callback'  => 'save'
				),
			);
		}

		public function getRoutes() {
			$routes = $this->_Parent->Database->fetch("SELECT * FROM tbl_router");
			return $routes;
        }

		public function save($context) {
			$routes = array();
			$route = array();
			
			// Copied the functionality from the sections page.
			// This meant I could not use the normal settings functionality from symphony.
			// There probably is a better way, but I could not find it.
			
			if(is_array($_POST['fields'])){
				foreach($_POST['fields'] as $post_values){
					if(is_array($post_values)){
						foreach($post_values as $key => $val){
							if($key == 'router'){
								if($val['redirect'] != 'yes'){
									$val['redirect'] = 'no';
								}
								$routes[] = $val;
							}
						}
					}
				}
			}
			
			$this->_Parent->Database->query("DELETE FROM tbl_router");
			if(!empty($routes)){
				$this->_Parent->Database->insert($routes, "tbl_router");
			}
			
			unset($context['settings']['router']['routes']);
			
		}

		public function addCustomPreferenceFieldsets($context){
			
			$i = -1;
			$fieldset = new XMLElement('fieldset');
			$fieldset->setAttribute('class', 'settings');
			$fieldset->appendChild(new XMLElement('legend', 'Regex URL re-routing'));
 
			$p = new XMLElement('p', 'Define regex rules for URL re-routing');
			$p->setAttribute('class', 'help');
			$fieldset->appendChild($p);

			$group = new XMLElement('div');
			$h3 = new XMLElement('h3', __('URL Schema Rules'));
			$h3->setAttribute('class', 'label');
			$group->appendChild($h3);
 
			$ol = new XMLElement('ol');
			$ol->setAttribute('id', 'fields-duplicator');
			$ol->setAttribute('class', 'orderable duplicator');
			$li = new XMLElement('li');
			$li->setAttribute('class', 'template');
			$labelfrom = Widget::Label(__('From'));
           	$labelfrom->appendChild(Widget::Input("fields[$i][router][from]", "From"));
            $labelto = Widget::Label(__('To'));
            $labelto->appendChild(Widget::Input("fields[$i][router][to]", "To"));
			$divgroup = new XMLElement('div');
			$divgroup->setAttribute('class', 'group');
			$divgroup->appendChild($labelfrom);
			$divgroup->appendChild($labelto);
			$divcontent = new XMLElement('div');
			$divcontent->setAttribute('class', 'content');
						
			//add checkbox per item, instead of per all.
			$labelcheck = Widget::Label();
			$inputcheck = Widget::Input("fields[$i][router][redirect]", 'yes', 'checkbox');
			$labelcheck->setValue($inputcheck->generate() . ' ' . __('Redirect legacy URLs to new destination'));
			
			$divcontent->appendChild($divgroup);
			
			$divcontent->appendChild($labelcheck);
			$divcontent->appendChild(new XMLElement('p', __('Redirects requests to the new destination instead of just displaying the content under the legacy URL.'), array('class' => 'help')));
			
								
			$li->appendChild(new XMLElement('h4', "Route"));
			$li->appendChild($divcontent);
			$ol->appendChild($li);
			$i++;
			if($routes = $this->getRoutes()) {
				if(is_array($routes)) {
					foreach($routes as $route) {
						$li = new XMLElement('li');
						$li->setAttribute('class', 'instance expanded');
						$h4 = new XMLElement('h4', 'Route');
						//$h4->appendChild(new XMLElement('span', 'Route'));
						$li->appendChild($h4);
						$divcontent = new XMLElement('div');
						$divcontent->setAttribute('class', 'content');
						$divgroup = new XMLElement('div');
						$divgroup->setAttribute('class', 'group');
						$labelfrom = Widget::Label(__('From'));
						$labelfrom->appendChild(Widget::Input("fields[$i][router][from]", General::sanitize($route['from'])));
						$labelto = Widget::Label(__('To'));
						$labelto->appendChild(Widget::Input("fields[$i][router][to]", General::sanitize($route['to'])));
						$divgroup->appendChild($labelfrom);
						$divgroup->appendChild($labelto);
						
						//place redirect checkbox in each box.
						$labelcheck = Widget::Label();
						$inputcheck = Widget::Input("fields[$i][router][redirect]", 'yes', 'checkbox');
						if($this->getRedirect($i) == true){
							$inputcheck->setAttribute('checked','checked');
						}
						$labelcheck->setValue($inputcheck->generate() . ' ' . __('Redirect legacy URLs to new destination'));
						
						$divcontent->appendChild($divgroup);
						
						$divcontent->appendChild($labelcheck);
						$divcontent->appendChild(new XMLElement('p', __('Redirects requests to the new destination instead of just displaying the content under the legacy URL.'), array('class' => 'help')));
												
						$li->appendChild($divcontent);
						$ol->appendChild($li);
						$i++;
					}
				}
			}
			$group->appendChild($ol);
			
			$fieldset->appendChild($group);
			$context['wrapper']->appendChild($fieldset);	
		}
		
		public function getRedirect($route_nr){
			$routes = $this->getRoutes();
			if(isset($routes[$route_nr-1])){
				if($routes[$route_nr-1]['redirect'] == 'yes'){
					return true;
				}
				else{
					return false;
				}
			}
			else{
				return false;
			}
		}

		public function frontendPrePageResolve($context) {
			$routes = $this->getRoutes();
			$url = $context['page'];
			$matches = array();
			$i = 1;
			foreach($routes as $route) {
				if(preg_match($route['from'], $url, &$matches) == 1) {
					$new_url = preg_replace($route['from'], $route['to'], $url);
					if($this->getRedirect($i)){
						header("Location:" . $new_url);
						die();
					}
					break;
				}
				$i++;
			}
			if($new_url) $context['page'] = $new_url;
		}
			
	}
