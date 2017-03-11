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
        $this->loadCmdFromConf();
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
		if (isset($device['name']) && !$_update) {
			$this->setName('[' . $this->getLogicalId() . ']' . $device['name']);
		}
		$this->import($device);
	}

    public function callAyah($param) {
        $url = 'http://api.alquran.cloud/ayah/' . $param . '/editions/ar.husarymujawwad,fr.leclerc,fr.hamidullah';
        $body = json_decode(file_get_contents($url), true);
        log::add('ayat', 'debug', 'recu : ' . print_r($body,true));
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
    public function callSourah($param) {
        $url = 'http://api.alquran.cloud/surah/' . $param . '/editions/ar.husarymujawwad,fr.leclerc,fr.hamidullah';
        $body = json_decode(file_get_contents($url), true);
        log::add('ayat', 'debug', 'recu : ' . print_r($body,true));
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
        $this->checkAndUpdateCmd('audio', $audio);
        $this->checkAndUpdateCmd('audiotranslation', $audiotranslation);
        $this->checkAndUpdateCmd('juz', $juz);

        $this->checkAndUpdateCmd('surah:name', $body['data'][1]['name']);
        $this->checkAndUpdateCmd('surah:englishName', $body['data'][1]['englishName']);
        $this->checkAndUpdateCmd('surah:englishNameTranslation', $body['data'][1]['englishNameTranslation']);
        $this->checkAndUpdateCmd('sura:number', $body['data'][1]['number']);
        $this->checkAndUpdateCmd('number', 0]);
        $this->checkAndUpdateCmd('numberInSurah', 0);
        $this->checkAndUpdateCmd('surah:revelationType', $body['data'][1]['revelationType']);
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
                        $eqLogic->callAyah($_options['title'] . ':' . $_options['message']);
                    } else {
                        $eqLogic->callSourah($_options['title']);
                    }
                } else {
                    if ($_options['message'] != '') {
                        //avec un ayat
                        $eqLogic->callAyah($_options['message']);
                    } else {
                        return;
                    }
                }
                break;
                case 'other':
                if ($this->getLogicalId() == 'randomAyat') {
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
