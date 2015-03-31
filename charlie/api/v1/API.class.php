<?php
abstract class API {
    /**
     * Property: method
     * The HTTP method this request was made in, either GET, POST, PUT or DELETE
     */
    protected $method = '';
    /**
     * Property: endpoint
     * The Model requested in the URI. eg: /files
     */
    protected $endpoint = '';
    /**
     * Property: verb
     * An optional additional descriptor about the endpoint, used for things that can
     * not be handled by the basic methods. eg: /files/process
     */
    protected $verb = '';
    /**
     * Property: args
     * Any additional URI components after the endpoint and verb have been removed, in our
     * case, an integer ID for the resource. eg: /<endpoint>/<verb>/<arg0>/<arg1>
     * or /<endpoint>/<arg0>
     */
    protected $args = Array();
    /**
     * Property: file
     * Stores the input of the PUT request
     */
     protected $file = Null;

    /**
     * Constructor: __construct
     * Allow for CORS, assemble and pre-process the data
     */
    public function __construct($request) {
        header("Access-Control-Allow-Orgin: *");
        header("Access-Control-Allow-Methods: *");
        header("Content-Type: application/json");

        $this->args = explode('/', rtrim($request, '/'));
        $this->endpoint = array_shift($this->args);
        if (array_key_exists(0, $this->args) && !is_numeric($this->args[0])) {
            //$this->verb = array_shift($this->args);
        }

        $this->method = $_SERVER['REQUEST_METHOD'];
        if ($this->method == 'POST' && array_key_exists('HTTP_X_HTTP_METHOD', $_SERVER)) {
            if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'DELETE') {
                $this->method = 'DELETE';
            } else if ($_SERVER['HTTP_X_HTTP_METHOD'] == 'PUT') {
                $this->method = 'PUT';
            } else {
                throw new Exception("Unexpected Header");
            }
        }

        switch($this->method) {
          case 'DELETE':
          case 'POST':
              $this->request = $this->_cleanInputs($_POST);
              break;
          case 'GET':
              $this->request = $this->_cleanInputs($_GET);
              break;
          case 'PUT':
            $this->request = $this->_cleanInputs($_GET);
            $this->file = file_get_contents("php://input");
            break;
          default:
            $this->_response('Invalid Method', 405);
            break;
        }
    }

    public function processAPI() {
      // Check that the session is active.
      if ($this -> endpoint != 'login' && $this -> endpoint != 'createUser' && $this -> endpoint != 'session' && !checkSession()) {
        return $this->_response("Not logged in.", 401);
      }
      switch($this ->endpoint) {
        case 'session':
          if (!checkSession()) {
            return $this->_response("Not logged in.", 401);
          } else {
            $json_array = [];
            $json_array['user_details'] = [];
            $json_array['user_details']['name'] = $_SESSION['login'];
            $json_array['user_details']['ip'] = $_SESSION['ip'];
            $json_array['user_details']['date'] = $_SESSION['date'];
            return json_encode($json_array);
          }
          break;
        case 'createUser':
        //createUser/name/USER_NAME/pass/USER_PASS/email/EMAIL
          if (empty($this->args)) {
            return $this->_response("Invalid parameter(s)", 400);
          }
          $this->args = array_map('strtoupper', $this->args);
          if (in_array('EMAIL', $this->args)) {
            $nextPos = array_search('EMAIL',$this->args)+1;
            if (!filter_var($this->args[$nextPos], FILTER_VALIDATE_EMAIL)) {
              return $this->_response("Invalid parameter(s)", 400);
            }
          } else {
            return $this->_response("Invalid parameter(s)", 400);
          }
          if (in_array('NAME', $this->args)) {
            $nextPos = array_search('NAME',$this->args)+1;
            if (checkIfUserExists($this->args[$nextPos])) {
              return $this->_response("Conflict", 409);
            }
          } else {
            return $this->_response("Invalid parameter(s)", 400);
          }
          break;
        case 'login':
          if (empty($this->args[0]) || empty($this->args[1])) {
            return $this->_response("Invalid parameter(s)", 400);
          }
          break;
        case 'quest':
          $this->args = array_map('strtoupper', $this->args);
          if (empty($this->args[0]) || !is_numeric($this->args[0])) {
            return $this->_response("Invalid parameter(s)", 400);
          }
          if (in_array('LIMIT',$this->args)) {
            $nextPos = array_search('LIMIT',$this->args)+1;
            if (count($this->args) >= $nextPos+1 && !is_numeric($this->args[$nextPos])) {
              return $this->_response("Invalid parameter(s)", 400);
            }
          }
          if (in_array('ORDER', $this->args)) {
            $nextPos = array_search('ORDER', $this->args) + 1;
            if (count($this->args) >= $nextPos+1 && strtoupper($this->args[$nextPos]) != 'ASC' && strtoupper($this->args[$nextPos]) != 'DESC') {
              return $this->_response("Invalid parameter(s)", 400);
            }
          }
          if (in_array('PAGE', $this->args)) {
            $nextPos = array_search('PAGE', $this->args) + 1;
            if (count($this->args) >= $nextPos+1 && !is_numeric($this->args[$nextPos])) {
              return $this->_response("Invalid parameter(s)", 400);
            }
          }
          break;
        case 'users':
          if (isset($this->args) && is_array($this->args) && count($this->args) >= 1) {
            if (count($this->args) && $this->args[0] == 'uid' && empty($this -> args[1]) || !is_numeric($this->args[1])) {
              return $this->_response("Invalid parameter(s)", 400);
            } else if (count($this->args) && $this->args[0] == 'qid' && empty($this -> args[1]) || !is_numeric($this->args[1])) {
              return $this->_response("Invalid parameter(s)", 400);
            } else if (count($this->args) && $this->args[0] == 'cid' && empty($this -> args[1]) || !is_numeric($this->args[1])) {
              return $this->_response("Invalid parameter(s)", 400);
            }
          }
          break;
        case 'posts':
          if (isset($this->args) && is_array($this->args) && count($this->args) >= 1) {
            if (!in_array('qid',$this->args) && !in_array('pid',$this->args) && !in_array('cid',$this->args) && !in_array('uid',$this->args)) {
              return $this->_response("Invalid parameter(s)", 400);
            }
            if (in_array('qid',$this->args) && in_array('pid',$this->args)) {
              return $this->_response("Invalid parameter(s)", 400);
            }
            if (in_array('pid',$this->args) && (in_array('cid',$this->args) || in_array('uid',$this->args))) {
              return $this->_response("Invalid parameter(s)", 400);
            }
            if (in_array('cid',$this->args) && in_array('uid',$this->args)) {
              return $this->_response("Invalid parameter(s)", 400);
            }
            
            if (in_array('qid',$this->args)) {
              $nextPos = array_search('qid',$this->args)+1;
              if (count($this->args) >= $nextPos+1 && !is_numeric($this->args[$nextPos])) {
                return $this->_response("Invalid parameter(s)", 400);
              }
            }
            if (in_array('pid',$this->args)) {
              $nextPos = array_search('pid',$this->args)+1;
              if (count($this->args) >= $nextPos+1 && !is_numeric($this->args[$nextPos])) {
                return $this->_response("Invalid parameter(s)", 400);
              }
            }
            if (in_array('cid',$this->args)) {
              $nextPos = array_search('cid',$this->args)+1;
              if (count($this->args) >= $nextPos+1 && !is_numeric($this->args[$nextPos])) {
                return $this->_response("Invalid parameter(s)", 400);
              }
            }
            if (in_array('uid',$this->args)) {
              $nextPos = array_search('uid',$this->args)+1;
              if (count($this->args) >= $nextPos+1 && !is_numeric($this->args[$nextPos])) {
                return $this->_response("Invalid parameter(s)", 400);
              }
            }
            if (in_array('limit',$this->args)) {
              $nextPos = array_search('limit',$this->args)+1;
              if (count($this->args) >= $nextPos+1 && !is_numeric($this->args[$nextPos])) {
                return $this->_response("Invalid parameter(s)", 400);
              }
            }
            if (in_array('asc',$this->args)) {
              $this->args[array_search('asc',$this->args)] = 'ASC';
            }
            if (in_array('desc',$this->args)) {
              $this->args[array_search('desc',$this->args)] = 'DESC';
            }
            if (in_array('ASC', $this->args) && in_array('DESC',$this->args)) {
              return $this->_response("Invalid parameter(s)", 400);
            }
          } else {
            return $this->_response("Invalid parameter(s)", 400);
          }
          break;
      }
      if ((int)method_exists($this, $this->endpoint) > 0) {
        if ($this->_response($this->{$this->endpoint}($this->args)) == '"null_results"') {
          return $this->_response("No results.", 404);
       } else  if ($this->_response($this->{$this->endpoint}($this->args)) == '"database_error"') {
          return $this->_response("Database error.", 500);
       } else {
          return $this->_response($this->{$this->endpoint}($this->args));
        }
      }
      return $this->_response("No Endpoint: $this->endpoint", 404);
    }

    private function _response($data, $status = 200) {
        header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
        return json_encode($data);
    }

    private function _cleanInputs($data) {
        $clean_input = Array();
        if (is_array($data)) {
            foreach ($data as $k => $v) {
                $clean_input[$k] = $this->_cleanInputs($v);
            }
        } else {
            $clean_input = trim(strip_tags($data));
        }
        return $clean_input;
    }

    private function _requestStatus($code) {
        $status = array(  
            200 => 'OK',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            404 => 'Not Found',   
            405 => 'Method Not Allowed',
            409 => 'Conflict',
            500 => 'Internal Server Error',
        ); 
        return ($status[$code])?$status[$code]:$status[500]; 
    }
}
?>