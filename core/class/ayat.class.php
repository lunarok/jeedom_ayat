<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

/* * ***************************Includes********************************* */
require_once dirname(__FILE__) . '/../../../../core/php/core.inc.php';

class ayat extends eqLogic {


    public function postUpdate() {
        $cmd = ayatCmd::byEqLogicIdAndLogicalId($this->getId(),'door');
        if (!is_object($cmd)) {
            $cmd = new ayatCmd();
            $cmd->setLogicalId('door');
            $cmd->setIsVisible(1);
            $cmd->setName(__('Ouverture Porte', __FILE__));
        }
        $cmd->setType('action');
        $cmd->setSubType('other');
        $cmd->setConfiguration('url','open-door.cgi');
        $cmd->setEqLogic_id($this->getId());
        $cmd->save();

        $cmd = ayatCmd::byEqLogicIdAndLogicalId($this->getId(),'dooropen');
        if (!is_object($cmd)) {
            $cmd = new ayatCmd();
            $cmd->setLogicalId('dooropen');
            $cmd->setIsVisible(1);
            $cmd->setName(__('Porte', __FILE__));
        }
        $cmd->setType('info');
        $cmd->setSubType('binary');
        $cmd->setDisplay('generic_type','LOCK_STATE');
        $cmd->setConfiguration('returnStateValue',1);
        $cmd->setConfiguration('returnStateTime',1);
        $cmd->setTemplate("mobile",'lock');
        $cmd->setTemplate("dashboard",'lock' );
        $cmd->setEqLogic_id($this->getId());
        $cmd->save();

    }

    public function getAyat() {

        log::add('ayat', 'debug', 'Retour : ' . $retour);
    }

    public function getSourate() {
        $retour = file_get_contents($url);
        log::add('ayat', 'debug', 'Retour : ' . $retour);
    }

}

class ayatCmd extends cmd {
    public function execute($_options = null) {
        switch ($this->getType()) {
            case 'info' :
            return $this->getConfiguration('value');
            break;
            case 'action' :
            $request = $this->getConfiguration('request');
            switch ($this->getSubType()) {
                case 'slider':
                $request = str_replace('#slider#', $value, $request);
                break;
                case 'color':
                $request = str_replace('#color#', $_options['color'], $request);
                break;
                case 'message':
                if ($_options != null)  {
                    $replace = array('#title#', '#message#');
                    $replaceBy = array($_options['title'], $_options['message']);
                    if ( $_options['title'] == '') {
                        throw new Exception(__('Le sujet ne peuvent être vide', __FILE__));
                    }
                    $request = str_replace($replace, $replaceBy, $request);

                }
                else
                $request = 1;
                break;
                default : $request == null ?  1 : $request;
            }

            return true;
        }
        return true;
    }
}

?>
