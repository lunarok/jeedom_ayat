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

    public function postAjax() {
        $this->loadCmdFromConf();
    }

    public function preSave() {
        $url = network::getNetworkAccess('external') . '/plugins/ayat/data/' . $this->getId() . '_arab.mp3';
        $this->setConfiguration('url',$url);
        $url = network::getNetworkAccess('external') . '/plugins/ayat/data/' . $this->getId() . '_fr.mp3';
        $this->setConfiguration('urlfr',$url);
    }

    public function loadCmdFromConf($_update = false) {
		if (!is_file(dirname(__FILE__) . '/../config/devices/ayat.json')) {
			return;
		}
		$content = file_get_contents(dirname(__FILE__) . '/../config/devices/ayat.json');
		if (!is_json($content)) {
			return;
		}
		$device = json_decode($content, true);
		if (!is_array($device) || !isset($device['commands'])) {
			return true;
		}
		/*$this->import($device);*/
        if (isset($device['configuration'])) {
			foreach ($device['configuration'] as $key => $value) {
				$this->setConfiguration($key, $value);
			}
		}
        foreach ($device['commands'] as $command) {
            $cmd = null;
            foreach ($this->getCmd() as $liste_cmd) {
                if ((isset($command['logicalId']) && $liste_cmd->getLogicalId() == $command['logicalId'])
                    || (isset($command['name']) && $liste_cmd->getName() == $command['name'])) {
                    $cmd = $liste_cmd;
                    break;
                }
            }
            if ($cmd == null || !is_object($cmd)) {
                $cmd = new ayatCmd();
                $cmd->setEqLogic_id($this->getId());
                utils::a2o($cmd, $command);
                $cmd->save();
            }
        }
	}

    public function callAyah($param) {
        $url = 'http://api.alquran.cloud/ayah/' . $param . '/editions/ar.' . $this->getConfiguration('recite') . ',fr.leclerc,fr.hamidullah';
        $body = json_decode(file_get_contents($url), true);
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
        $this->dwldAyat($body['data'][0]['audio'],'arab');
        $this->dwldAyat($body['data'][1]['audio'],'fr');
    }

    public function callExtract($sourate,$param) {
        $sub = explode('-', $param);
        $arabic = $translation = $juz = '';
        $audio = $audiotranslation = [];
        for ($i=$sub[0]; $i <= $sub[1]; $i++) {
            if ($sourate == 0) {
                $param = $i;
            } else {
                $param = $sourate . ':' . $i;
            }
            $url = 'http://api.alquran.cloud/ayah/' . $param . '/editions/ar.' . $this->getConfiguration('recite') . ',fr.leclerc,fr.hamidullah';
            $body = json_decode(file_get_contents($url), true);
            $arabic .= $body['data'][0]['text'] . ' - ' . $body['data'][0]['numberInSurah'];
            $translation .= $body['data'][2]['numberInSurah'] . ' - ' . $body['data'][2]['text'];
            $audio[] = $body['data'][0]['audio'];
            $audiotranslation[] = $body['data'][1]['audio'];
        }
        $this->checkAndUpdateCmd('arabic', $arabic);
        $this->checkAndUpdateCmd('translation', $translation);
        $this->checkAndUpdateCmd('audio', json_encode($audio));
        $this->checkAndUpdateCmd('audiotranslation', json_encode($audiotranslation));
        $this->checkAndUpdateCmd('surah:name', $body['data'][1]['surah']['name']);
        $this->checkAndUpdateCmd('surah:englishName', $body['data'][1]['surah']['englishName']);
        $this->checkAndUpdateCmd('surah:englishNameTranslation', $body['data'][1]['surah']['englishNameTranslation']);
        $this->checkAndUpdateCmd('sura:number', $body['data'][1]['surah']['number']);
        $this->checkAndUpdateCmd('number', $body['data'][1]['number']);
        $this->checkAndUpdateCmd('numberInSurah', $body['data'][1]['numberInSurah']);
        $this->checkAndUpdateCmd('juz', $body['data'][1]['juz']);
        $this->checkAndUpdateCmd('surah:revelationType', $body['data'][1]['surah']['revelationType']);
        $this->dwldAyat(implode(',', $audio),'arab');
        $this->dwldAyat(implode(',', $audiotranslation),'fr');
    }

    public function callSourah($param) {
        $url = 'http://api.alquran.cloud/surah/' . $param . '/editions/ar.' . $this->getConfiguration('recite') . ',fr.leclerc,fr.hamidullah';
        $body = json_decode(file_get_contents($url), true);
        $arabic = $translation = $juz = '';
        $audio = $audiotranslation = [];
        foreach ($body['data'][0]['ayahs'] as $ayah) {
            $arabic .= $ayah['text'];
            $audio[] = $ayah['audio'];
            $juz = $ayah['juz'];
        }
        foreach ($body['data'][1]['ayahs'] as $ayah) {
            $audiotranslation[] = $ayah['audio'];
        }
        foreach ($body['data'][2]['ayahs'] as $ayah) {
            $translation .= $ayah['text'];
        }
        $this->checkAndUpdateCmd('arabic', $arabic);
        $this->checkAndUpdateCmd('translation', $translation);
        $this->checkAndUpdateCmd('audio', json_encode($audio));
        $this->checkAndUpdateCmd('audiotranslation', json_encode($audiotranslation));
        $this->checkAndUpdateCmd('juz', $juz);

        $this->checkAndUpdateCmd('surah:name', $body['data'][1]['name']);
        $this->checkAndUpdateCmd('surah:englishName', $body['data'][1]['englishName']);
        $this->checkAndUpdateCmd('surah:englishNameTranslation', $body['data'][1]['englishNameTranslation']);
        $this->checkAndUpdateCmd('sura:number', $body['data'][1]['number']);
        $this->checkAndUpdateCmd('number', 0);
        $this->checkAndUpdateCmd('numberInSurah', 0);
        $this->checkAndUpdateCmd('surah:revelationType', $body['data'][1]['revelationType']);
        $this->dwldAyat(implode(',', $audio),'arab');
        $this->dwldAyat(implode(',', $audiotranslation),'fr');
    }

    public function dwldAyat($list,$lang) {
		if (!file_exists(dirname(__FILE__) . '/../../data')) {
			mkdir(dirname(__FILE__) . '/../../data');
		}
        log::add('ayat', 'debug', 'list : ' . $list);
        $data_path = realpath(dirname(__FILE__)) . '/../../data/' . $this->getId() . '_' . $lang . '.mp3';
        $resource_path = realpath(dirname(__FILE__) . '/../../resources');
        log::add('ayat', 'debug', $resource_path . '/dwld.sh "' . $list . '" ' . $data_path );
        passthru('/bin/bash ' . $resource_path . '/dwld.sh "' . $list . '" ' . $data_path);
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
                        if (strpos($_options['message'],'-') === false) {
                            $eqLogic->callAyah($_options['title'] . ':' . $_options['message']);
                        } else {
                            $eqLogic->callExtract($_options['title'], $_options['message']);
                        }
                    } else {
                        $eqLogic->callSourah($_options['title']);
                    }
                } else {
                    if ($_options['message'] != '') {
                        //avec un ayat
                        if (strpos($_options['message'],'-') === false) {
                            $eqLogic->callAyah($_options['message']);
                        } else {
                            $eqLogic->callExtract(0, $_options['message']);
                        }
                    } else {
                        return;
                    }
                }
                break;
                case 'other':
                if ($this->getLogicalId() == 'playAr') {
                    $play = $eqLogic->getConfiguration('play');
                    if ($play != '') {
                        $options['title'] = $eqLogic->getConfiguration('url');
                        $options['message'] = $eqLogic->getConfiguration('url');
                        $cmd = cmd::byId(str_replace("#", "", $play));
                        $cmd->execCmd($options);
                    }
                } else if ($this->getLogicalId() == 'playFr') {
                    $play = $eqLogic->getConfiguration('play');
                    if ($play != '') {
                        $options['title'] = $eqLogic->getConfiguration('urlfr');
                        $options['message'] = $eqLogic->getConfiguration('urlfr');
                        $cmd = cmd::byId(str_replace("#", "", $play));
                        $cmd->execCmd($options);
                    }
                } else if ($this->getLogicalId() == 'randomAyat') {
                    $eqLogic->callAyah(rand(1,6236));
                } else {
                    $eqLogic->callSourah(rand(1,114));
                }
                break;
            }
            return true;
        }
        return true;
    }
}

?>
