<?php
/**
 * @package     Gerens/CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author      João Vinezof
 * 
 */
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * 
 * Classe responsavel por adicionar loads
 * 
 * @package     Gerens/CodeIgniter
 * @subpackage	Libraries
 * @category	Libraries
 * @author      João Vinezof
 * 
 */
class MY_Loader extends CI_Loader
{

    /**
     * List of loaded services
     *
     * @var	array
     */
    protected $_ci_services = array();

    /**
     * List of paths to load services from
     *
     * @var	array
     */
    protected $_ci_service_paths = array(APPPATH);


    /**
     * List of loaded repositories
     *
     * @var	array
     */
    protected $_ci_repositories = array();

    /**
     * List of paths to load repositories from
     *
     * @var	array
     */
    protected $_ci_repository_paths = array(APPPATH);

    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Carrega um service
     * 
     * @param string $service Nome da classe que vai ser carregada
     * @param string $name = $service Nome da variavel que vai ser criada com um objeto da classe
     * @param bool $db_conn = false indica se vai ser feita conexão com o banco de dados
     */
    public function service($service, $name = '', $db_conn = FALSE)
    {
        if (empty($service)) {
            return $this;
        } elseif (is_array($service)) {
            foreach ($service as $key => $value) {
                is_int($key) ? $this->service($value, '', $db_conn) : $this->service($key, $value, $db_conn);
            }

            return $this;
        }

        $path = '';

        // Is the service in a sub-folder? If so, parse out the filename and path.
        if (($last_slash = strrpos($service, '/')) !== FALSE) {
            // The path is in front of the last slash
            $path = substr($service, 0, ++$last_slash);

            // And the service name behind it
            $service = substr($service, $last_slash);
        }

        if (empty($name)) {
            $name = $service;
        }

        if (in_array($name, $this->_ci_services, TRUE)) {
            return $this;
        }

        $CI = &get_instance();
        if (isset($CI->$name)) {
            throw new RuntimeException('The service name you are loading is the name of a resource that is already being used: ' . $name);
        }

        if ($db_conn !== FALSE && !class_exists('CI_DB', FALSE)) {
            if ($db_conn === TRUE) {
                $db_conn = '';
            }

            $this->database($db_conn, FALSE, TRUE);
        }

        // Note: All of the code under this condition used to be just:
        //
        //       load_class('Service', 'core');
        //
        //       However, load_class() instantiates classes
        //       to cache them for later use and that prevents
        //       MY_Service from being an abstract class and is
        //       sub-optimal otherwise anyway.
        if (!class_exists('CI_Service', FALSE)) {
            $app_path = APPPATH . 'core' . DIRECTORY_SEPARATOR;
            if (file_exists($app_path . 'Service.php')) {
                require_once($app_path . 'Service.php');
                if (!class_exists('CI_Service', FALSE)) {
                    throw new RuntimeException($app_path . "Service.php exists, but doesn't declare class CI_Service");
                }

                log_message('info', 'CI_Service class loaded');
            } elseif (!class_exists('CI_Service', FALSE)) {
                require_once(BASEPATH . 'core' . DIRECTORY_SEPARATOR . 'Service.php');
            }

            $class = config_item('subclass_prefix') . 'Service';
            if (file_exists($app_path . $class . '.php')) {
                require_once($app_path . $class . '.php');
                if (!class_exists($class, FALSE)) {
                    throw new RuntimeException($app_path . $class . ".php exists, but doesn't declare class " . $class);
                }

                log_message('info', config_item('subclass_prefix') . 'Service class loaded');
            }
        }

        $service = ucfirst($service);
        if (!class_exists($service, FALSE)) {
            foreach ($this->_ci_service_paths as $mod_path) {
                if (!file_exists($mod_path . 'services/' . $path . $service . '.php')) {
                    continue;
                }

                require_once($mod_path . 'services/' . $path . $service . '.php');
                if (!class_exists($service, FALSE)) {
                    throw new RuntimeException($mod_path . "services/" . $path . $service . ".php exists, but doesn't declare class " . $service);
                }

                break;
            }

            if (!class_exists($service, FALSE)) {
                throw new RuntimeException('Unable to locate the service you have specified: ' . $service);
            }
        } elseif (!is_subclass_of($service, 'CI_Service')) {
            throw new RuntimeException("Class " . $service . " already exists and doesn't extend CI_Service");
        }

        $this->_ci_sercices[] = $name;
        $service = new $service();
        $CI->$name = $service;
        log_message('info', 'Service "' . get_class($service) . '" initialized');
        return $this;
    }

    /**
     * Carrega um repositorio
     * 
     * @param string $repository Nome da classe que vai ser carregada
     * @param string $name = $repository Nome da variavel que vai ser criada com um objeto da classe
     * @param bool $db_conn = false indica se vai ser feita conexão com o banco de dados
     */
    public function repository($repository, $name = '', $db_conn = FALSE)
    {
        if (empty($repository)) {
            return $this;
        } elseif (is_array($repository)) {
            foreach ($repository as $key => $value) {
                is_int($key) ? $this->repository($value, '', $db_conn) : $this->repository($key, $value, $db_conn);
            }

            return $this;
        }

        $path = '';

        // Is the repository in a sub-folder? If so, parse out the filename and path.
        if (($last_slash = strrpos($repository, '/')) !== FALSE) {
            // The path is in front of the last slash
            $path = substr($repository, 0, ++$last_slash);

            // And the repository name behind it
            $repository = substr($repository, $last_slash);
        }

        if (empty($name)) {
            $name = $repository;
        }

        if (in_array($name, $this->_ci_repositories, TRUE)) {
            return $this;
        }

        $CI = &get_instance();
        if (isset($CI->$name)) {
            throw new RuntimeException('The repository name you are loading is the name of a resource that is already being used: ' . $name);
        }

        if ($db_conn !== FALSE && !class_exists('CI_DB', FALSE)) {
            if ($db_conn === TRUE) {
                $db_conn = '';
            }

            $this->database($db_conn, FALSE, TRUE);
        }

        // Note: All of the code under this condition used to be just:
        //
        //       load_class('Repository', 'core');
        //
        //       However, load_class() instantiates classes
        //       to cache them for later use and that prevents
        //       MY_Repository from being an abstract class and is
        //       sub-optimal otherwise anyway.
        if (!class_exists('CI_Repository', FALSE)) {
            $app_path = APPPATH . 'core' . DIRECTORY_SEPARATOR;
            if (file_exists($app_path . 'Repository.php')) {
                require_once($app_path . 'Repository.php');
                if (!class_exists('CI_Repository', FALSE)) {
                    throw new RuntimeException($app_path . "Repository.php exists, but doesn't declare class CI_Repository");
                }

                log_message('info', 'CI_Repository class loaded');
            } elseif (!class_exists('CI_Repository', FALSE)) {
                require_once(BASEPATH . 'core' . DIRECTORY_SEPARATOR . 'Repository.php');
            }

            $class = config_item('subclass_prefix') . 'Repository';
            if (file_exists($app_path . $class . '.php')) {
                require_once($app_path . $class . '.php');
                if (!class_exists($class, FALSE)) {
                    throw new RuntimeException($app_path . $class . ".php exists, but doesn't declare class " . $class);
                }

                log_message('info', config_item('subclass_prefix') . 'Repository class loaded');
            }
        }

        $repository = ucfirst($repository);
        if (!class_exists($repository, FALSE)) {
            foreach ($this->_ci_repository_paths as $mod_path) {
                if (!file_exists($mod_path . 'daos/' . $path . $repository . '.php')) {
                    continue;
                }

                require_once($mod_path . 'daos/' . $path . $repository . '.php');
                if (!class_exists($repository, FALSE)) {
                    throw new RuntimeException($mod_path . "daos/" . $path . $repository . ".php exists, but doesn't declare class " . $repository);
                }

                break;
            }

            if (!class_exists($repository, FALSE)) {
                throw new RuntimeException('Unable to locate the repository you have specified: ' . $repository);
            }
        } elseif (!is_subclass_of($repository, 'CI_Repository')) {
            throw new RuntimeException("Class " . $repository . " already exists and doesn't extend CI_Repository");
        }

        $this->_ci_sercices[] = $name;
        $repository = new $repository();
        $CI->$name = $repository;
        log_message('info', 'Repository "' . get_class($repository) . '" initialized');
        return $this;
    }
}
