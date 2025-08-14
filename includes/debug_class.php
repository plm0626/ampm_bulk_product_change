<?php
defined( 'ABSPATH' ) || exit; // block direct access to plugin PHP files by adding this line at the top of each of them

/**
 * Log message class
 * This class outputs a message to a file on the server.  The file is located in the ./wp-content/plugins/plm_apis/logfile_close
 *  directory.  The file is a .text file and begins with the UTC Date of the messaage and contains the user # followed by  '_log'.
 *  To output to the logfile simply create a new clss (logMsg) variable with the message you want in the ffile as the only argument.
 *  To output the message to the console create a new message from the class on 'echo' the message.  This will output a javaascript
 *  to place the message on the console.
 */
if ( ! class_exists( 'deBug' ) )
{
  class deBug {
    // Properties
    var $msg;
    // Methods
    function __construct($orig_msg) {
      $this->timezone = 'UTC';
      $this->orig_msg = $orig_msg;
      $this->printable = $this->get_printable();
      $this->msg = $this->get_msg();
      $this->console = $this->console_log_output();
    }

    function get_file_name() {
      $plugin_path = './wp-content/plugins/'.plugin_name().'/logfiles'; //ensure the function plugin_name() is in the plugin main dir.
      $date = $this->get_file_date();
      $user = $this->get_user();
      return $plugin_path.'/'.$date.$user."_log.txt";
    }

    function get_file_date() {
      $date = new DateTime();
      $this->date = $date->format('Y-m-d');
      return $this->date;
    }

    function get_msg_timestamp() {
      $date = new DateTime();
      $this->timestamp = $date->format('H:i:s');
      return $this->timestamp;
    }

    function get_msg() {
      $msg_type = gettype($this->orig_msg);
      switch ($msg_type) {
        case 'string':
          $msg_out = "(".'('.$this->timezone.')'.$this->get_msg_timestamp().")"."(".$msg_type.")".$this->orig_msg."\n";
          break;
        case 'object':
          $msg_out = "(".'('.$this->timezone.')'.$this->get_msg_timestamp().")"."(".$msg_type.")".json_encode($this->orig_msg)."\n";
          break;
        case 'array':
          $msg_out = "(".'('.$this->timezone.')'.$this->get_msg_timestamp().")"."(".$msg_type.")".implode(', ',$this->orig_msg)."\n";
          break;
        default:
          $msg_out = "(".'('.$this->timezone.')'.$this->get_msg_timestamp().")"."(".$msg_type.")".json_encode($this->orig_msg)."\n";
          break;
      }
      return sanitize_text_field($msg_out);
    }

    function get_debug_msg() {
      $msg_type = gettype($this->orig_msg);
      switch ($msg_type) {
        case 'string':
          $msg_out = $this->orig_msg;
          break;
        case 'object':
          $msg_out = json_encode($this->orig_msg);
          break;
        case 'array':
          $msg_out = implode(', ',$this->orig_msg);
          break;
        default:
          $msg_out = json_encode($this->orig_msg);
          break;
      }
      return sanitize_text_field($msg_out);
    }

    function get_user() {
      $this->user = "_user_".get_current_user_id();
      return $this->user;
    }

    function get_msg_out_for_logfile() {
      $this->msg_out = implode(',',[
        $this->timestamp,
        $this->msg,
      ]);
      return $this->msg_out;
    }

    function get_printable() {
      $this->printable = print_r($this->orig_msg, 1);;
      return $this->printable;
    }

    function logfile_open() {
      $this->file = fopen($this->get_file_name(),'a');
      return $this->file;
    }
    function logfile_write() {
      $this->write = fwrite($this->file,$this->msg);
      return $this->write;
    }
    function logfile_close() {
      $this->close = fclose($this->file);
      return $this->close;
    }

    function console_log_output() {
      $console_str = "<script>console.log('" .$this->get_debug_msg()."');</script>";
      echo $console_str;
    return $console_str;
    }

  }
}
?>
