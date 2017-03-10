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

    public function postSave() {
        $this->applyModuleConfiguration($this->getConfiguration('model'));
    }

    public static function devicesParameters($_device = '') {
        $return = array();
        foreach (ls(dirname(__FILE__) . '/../config/devices', '*') as $dir) {
            $path = dirname(__FILE__) . '/../config/devices/' . $dir;
            if (!is_dir($path)) {
                continue;
            }
            $files = ls($path, '*.json', false, array('files', 'quiet'));
            foreach ($files as $file) {
                try {
                    $content = file_get_contents($path . '/' . $file);
                    if (is_json($content)) {
                        $return += json_decode($content, true);
                    }
                } catch (Exception $e) {

                }
            }
        }
        if (isset($_device) && $_device != '') {
            if (isset($return[$_device])) {
                return $return[$_device];
            }
            return array();
        }
        return $return;
    }

    public function applyModuleConfiguration($model) {
        $device = self::devicesParameters($model);
        if (!is_array($device)) {
            return true;
        }

        $link_cmds = array();
        $link_actions = array();
        foreach ($device['commands'] as $command) {
            $ayatCmd = ayatCmd::byEqLogicIdAndLogicalId($this->getId(),$command['logicalId']);
            if (!is_object($ayatCmd)) {
                $ayatCmd = new ayatCmd();
                $ayatCmd->setEqLogic_id($this->getId());
                $ayatCmd->setEqType('ayat');
                $ayatCmd->setLogicalId($command['logicalId']);
                utils::a2o($ayatCmd, $command);
                $ayatCmd->save();
                if (isset($command['value'])) {
                    $link_cmds[$ayatCmd->getId()] = $command['value'];
                }
                if (isset($command['configuration']) && isset($command['configuration']['updateCmdId'])) {
                    $link_actions[$ayatCmd->getId()] = $command['configuration']['updateCmdId'];
                }
            }
        }
        if (count($link_cmds) > 0) {
            foreach ($this->getCmd() as $eqLogic_cmd) {
                foreach ($link_cmds as $cmd_id => $link_cmd) {
                    if ($link_cmd == $eqLogic_cmd->getName()) {
                        $cmd = cmd::byId($cmd_id);
                        if (is_object($cmd)) {
                            $cmd->setValue($eqLogic_cmd->getId());
                            $cmd->save();
                        }
                    }
                }
            }
        }
        if (count($link_actions) > 0) {
            foreach ($this->getCmd() as $eqLogic_cmd) {
                foreach ($link_actions as $cmd_id => $link_action) {
                    if ($link_action == $eqLogic_cmd->getName()) {
                        $cmd = cmd::byId($cmd_id);
                        if (is_object($cmd)) {
                            $cmd->setConfiguration('updateCmdId', $eqLogic_cmd->getId());
                            $cmd->save();
                        }
                    }
                }
            }
        }
    }

    public function callAPI($param) {
        $url = 'http://api.alquran.cloud/' . $param . '/editions/ar.husarymujawwad,fr.leclerc,fr.hamidullah';
        $body = json_decode(file_get_contents($url, true));

        $this->checkAndUpdateCmd('arabic', $body['data'][0]['text']);
        $this->checkAndUpdateCmd('translation', $body['data'][2]['text']);
        $this->checkAndUpdateCmd('audio', $body['data'][0]['audio']);
        $this->checkAndUpdateCmd('audiotranslation', $body['data'][1]['audio']);
        $this->checkAndUpdateCmd('surah:name', $body['data'][1]['surah']['name']);
        $this->checkAndUpdateCmd('surah:englishName', $body['data'][1]['surah']['englishName']);
        $this->checkAndUpdateCmd('surah:englishNameTranslation', $body['data'][1]['surah']['englishNameTranslation']);
        $this->checkAndUpdateCmd('sura:number', $body['data'][1]['surah']['number']);
        $this->checkAndUpdateCmd('number', $body['data'][1]['number']);
        $this->checkAndUpdateCmd('numberInSurah', $body['data'][1]['numberInSurah']);
        $this->checkAndUpdateCmd('juz', $body['data'][1]['juz']);
        $this->checkAndUpdateCmd('surah:revelationType', $body['data'][1]['surah']['revelationType']);
    }

}

class ayatCmd extends cmd {
    public function execute($_options = null) {
        switch ($this->getType()) {
            case 'info' :
            return $this->getConfiguration('value');
            break;
            case 'action' :
            $eqLogic = $this->getEqLogic();
            switch ($this->getSubType()) {
                case 'message':
                if ($_options['title'] != '') {
                    //contient un numéro de sourate
                    if ($_options['message'] != '') {
                        //avec un ayat
                        $param = 'ayah/' . $_options['title'] . ':' . $_options['message'];
                    } else {
                        $param = 'surah/' . $_options['title'];
                    }
                } else {
                    if ($_options['message'] != '') {
                        //avec un ayat
                        $param = 'ayah/' . $_options['message'];
                    } else {
                        return;
                    }
                }
                break;
                case 'other':
                if ($this->getLogicalId() == 'randomAyat') {
                    $param = 'ayah/' . rand(1,6236);
                } else {
                    $param = 'surah/' . rand(1,114);
                }
                break;
            }
            $eqLogic->callAPI($param);
            return true;
        }
        return true;
    }
}

?>
