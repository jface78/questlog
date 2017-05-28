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
        $this->request = $this->_cleanInputs($_GET);
        break;
      case 'POST':
        //print_r($_POST);
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
    if ($this -> endpoint != 'login' && $this -> endpoint != 'session' && !checkSession()) {
      return $this->_response("Not logged in.", 401);
    }
    switch($this ->endpoint) {
      case 'session':
        if (!checkSession()) {
          return $this->_response("Not logged in.", 401);
        }
        break;
      case 'login':
        if (empty($this->request['name']) || empty($this->request['pass'])) {
          return $this->_response("Invalid parameter(s)", 400);
        }
        break;
      case 'quests':
        if (!checkSession()) {
          return $this->_response("Not logged in.", 401);
        }
        break;
      case 'users':
        break;
      case 'posts':
        break;
      case 'search':
        break;
    }
    if ((int)method_exists($this, $this->endpoint) > 0) {
      $response = $this->{$this->endpoint}($this->request);
      if ($response == 'success') {
        return $this->_response('OK', 200);
      } else if ($response == 'null_results') {
        return $this->_response('Not Found', 404);
      } else  if ($response == 'database_error') {
        return $this->_response('Internal Error', 500);
      } else if ($response == 'duplicate_entry') {
        return $this -> _response('Conflict', 409);
      } else if ($response == 'unauthorized') {
        return $this -> _response('Unauthorized', 401);
      } else {
        return $this->_response($response);
      }
    } else {
      return $this->_response('No Endpoint: ' . $this->endpoint, 404);
    }
  }

  private function _response($data, $status = 200) {
    header("HTTP/1.1 " . $status . " " . $this->_requestStatus($status));
    return json_encode($data, JSON_UNESCAPED_SLASHES);
  }

  private function _cleanInputs($data) {
    $clean_input = Array();

    // hack for jquery not passing DELETE params.
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
      403 => 'Forbidden',
      404 => 'Not Found',   
      405 => 'Method Not Allowed',
      409 => 'Conflict',
      500 => 'Internal Server Error'
    ); 
    return ($status[$code])?$status[$code]:$status[500]; 
  }
}
?>