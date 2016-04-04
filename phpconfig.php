<?php
/*
 *      PHP Config Plug-in
 *      @package PHP Config Plug-in
 *      @author José António Cidre Bardelás
 *      @copyright Copyright (C) 2011 José António Cidre Bardelás and Joomla Empresa. All rights reserved
 *      @license GNU/GPL v3 or later
 *      
 *      Contact us at info@joomlaempresa.com (http://www.joomlaempresa.es)
 *      
 *      This file is part of PHP Config Plug-in.
 *      
 *          PHP Config Plug-in is free software: you can redistribute it and/or modify
 *          it under the terms of the GNU General Public License as published by
 *          the Free Software Foundation, either version 3 of the License, or
 *          (at your option) any later version.
 *      
 *          PHP Config Plug-in is distributed in the hope that it will be useful,
 *          but WITHOUT ANY WARRANTY; without even the implied warranty of
 *          MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *          GNU General Public License for more details.
 *      
 *          You should have received a copy of the GNU General Public License
 *          along with PHP Config Plug-in.  If not, see <http://www.gnu.org/licenses/>.
 */
defined('_JEXEC') or die('Acesso Restrito');
jimport('joomla.plugin.plugin');
jimport('joomla.version');

class plgSystemPHPConfig extends JPlugin {

		protected $_versom_joomla = null;
		protected $_plugin = null;
		protected $_params = null;

		function plgSystemPHPConfig(&$subject, $config) {
			parent::__construct($subject, $config);
			$versom = new JVersion;
			$this->_versom_joomla = substr($versom->getShortVersion(),0,3);
			
			if ($this->_versom_joomla == "1.5") {
				$this->_plugin = JPluginHelper::getPlugin('system', 'phpconfig');
				$params = new JParameter($this->_plugin->params);
			}
			else {
				$this->loadLanguage();
			}
			$chavesParams = array('amostrar' => 'frontend', 'endereco_ip' => '0', 'erros' => 'off', 'nivel_erro' => 'todos', 'max_exectime' => '0', 'memory_limit' => '0');
			foreach ($chavesParams as $chave => $valor) {
				if ($this->_versom_joomla == "1.5") {
					$this->_params[$chave] = $params->get($chave, $valor);
				}
				else {
					$this->_params[$chave] = $this->params->def($chave, $valor);
				}
			}
		}

		function onAfterInitialise() {
				if ($this->_versom_joomla == "1.5") {
					global $mainframe;
				}
				else {
					$mainframe = JFactory::getApplication();
				}
				if($this->_params['amostrar'] != 'todo') {
						if((!$mainframe->isSite() || $mainframe->isAdmin()) && $this->_params['amostrar'] == 'frontend') 
								return;
						if(($mainframe->isSite() || !$mainframe->isAdmin()) && $this->_params['amostrar'] == 'backend') 
								return;
				}
				$ip_real = $_SERVER['REMOTE_ADDR'];
				$endereco_ip = $this->_params['endereco_ip'];
				if($endereco_ip == '0' || in_array($ip_real, explode(",", $endereco_ip)) ) {
					@ini_set('display_errors', $this->_params['erros']);
					if($this->_params['erros'] == 'on') {
						switch($this->_params['nivel_erro']) {
								/*case 'strict':
									$nivel_erro = E_STRICT;
									break;*/
								case 'todos':
								$nivel_erro = E_ALL;
								break;
								case 'erros_execucom':
								$nivel_erro = E_ERROR| E_WARNING| E_PARSE;
								break;
								case 'erros_execucom_avisos':
								$nivel_erro = E_ERROR| E_WARNING| E_PARSE| E_NOTICE;
								break;
								case 'todos_menos_avisos':
								$nivel_erro = E_ALL^ E_NOTICE;
								break;
								default:
								$nivel_erro = E_ALL;
						}
						@ini_set('error_reporting', $nivel_erro);
					}
				if($this->_params['max_exectime'] != '0')
						@ini_set('max_execution_time', (int)$this->_params['max_exectime']);
				if($this->_params['memory_limit'] != '0' && preg_match('/(\d+[KMG])/', $this->_params['memory_limit'], $ocorrencias))
						@ini_set('memory_limit', $ocorrencias[0]);
				}
		}
}
