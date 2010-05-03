<?php
/**
 *
 * This file is part of openLibrary.
 * Copyright © 2009, Dansk Bibliotekscenter a/s,
 * Tempovej 7-11, DK-2750 Ballerup, Denmark. CVR: 15149043
 *
 * openLibrary is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * openLibrary is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with openLibrary.  If not, see <http://www.gnu.org/licenses/>.
*/


require_once("OLS_class_lib/webServiceServer_class.php");
require_once("OLS_class_lib/oci_class.php");

class openNumberRoll extends webServiceServer {

  function numberRoll($param) {
    if (!$this->aaa->has_right("opennumberroll", 500))
      $res->error->_value = "authentication_error";
    else {
      $valid_roll = $this->config->get_value("valid_number_roll","setup");
      if (in_array($param->numberRollName->_value, $valid_roll)) {
        $oci = new Oci($this->config->get_value("numberroll_credentials","setup"));
        $oci->set_charset("UTF8");
        try { $oci->connect(); }
        catch (ociException $e) {
          verbose::log(FATAL, "OpenNumberRoll:: OCI connect error: " . $oci->get_error_string());
          $res->error->_value = "error_reaching_database";
        }
        if (empty($res->error->_value)) 
          try {
            $oci->set_query("SELECT " . $param->numberRollName->_value . ".nextval FROM dual");
            $val = $oci->fetch_into_assoc();
          } catch (ociException $e) {
            verbose::log(FATAL, "OpenNumberRoll:: OCI select error: " . $oci->get_error_string());
            $res->error->_value = "error_creatingnumber_roll";
          }
        if (empty($res->error->_value) && ($nr = $val["NEXTVAL"]))
          $res->rollNumber->_value = $nr;
      } else
        $res->error->_value = "unknown_number_roll_name";
    }
    

    //var_dump($res); var_dump($param); die();
    $ret->numberRollResponse->_value = $res;
    return $ret;

  }

}

/*
 * MAIN
 */

$ws=new openNumberRoll('opennumberroll.ini');
$ws->handle_request();

?>

