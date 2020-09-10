<?php
if (!defined('BASEPATH')) exit ('No direct script access allowed');

class SwUpload extends CI_Upload
{

    var $multi_data = array ();
    var $multi_error_msg = array ();

    function __construct()
    {
        parent::__construct();
    }

    function do_multi_upload($field, $skip_error = array ())
    {
        if (! empty($_FILES[$field]))
        {
            foreach ($_FILES[$field]['name']AS $index=>$val)
            {
                if (in_array($_FILES[$field]['error'][$index], $skip_error))
                {
                    continue ;
                }

                foreach ($_FILES[$field] as $key=>$val_arr)
                {
                    $_FILES[$field.$index][$key] = $val_arr[$index];
                }

                if (self::do_upload($field.$index))
                {
                    $this->multi_data[$index] = self::data();
                }
                else
                {
                    $this->multi_error_msg[] = $val.': '.mb_strtolower($this->display_errors('', ''));
                    $this->error_msg = array ();
                }
            }
            unset ($_FILES[$field]);
            return (count($this->multi_data) > 0);
        }
    }

    function multi_data()
    {
        return $this->multi_data;
    }

    function display_multi_errors($open = '<p>', $close = '</p>')
    {
        return $open.implode($close.$open, $this->multi_error_msg).$close;
    }
}
