<?php

if (!isConnect('admin')) {
    throw new Exception('{{401 - Accès non autorisé}}');
}
sendVarToJS('eqType', 'ayat');
$eqLogics = eqLogic::byType('ayat');

?>

<div class="row row-overflow">
  <div class="col-lg-2 col-sm-3 col-sm-4" id="hidCol" style="display: none;">
    <div class="bs-sidebar">
      <ul id="ul_eqLogic" class="nav nav-list bs-sidenav">
        <li class="filter" style="margin-bottom: 5px;"><input class="filter form-control input-sm" placeholder="{{Rechercher}}" style="width: 100%"/></li>
        <?php
        foreach ($eqLogics as $eqLogic) {
          echo '<li class="cursor li_eqLogic" data-eqLogic_id="' . $eqLogic->getId() . '"><a>' . $eqLogic->getHumanName(true) . '</a></li>';
        }
        ?>
      </ul>
    </div>
  </div>

  <div class="col-lg-12 eqLogicThumbnailDisplay" id="listCol">

    <legend><i class="fas fa-cog"></i>  {{Gestion}}</legend>
    <div class="eqLogicThumbnailContainer">
      <div class="cursor eqLogicAction" data-action="add">
          <i class="fas fa-plus-circle" style="color:#00979c;"></i>
          <br/>
        <span style="color:#00979c">{{Ajouter}}</span>
      </div>

      <div class="cursor eqLogicAction" data-action="gotoPluginConf">
          <i class="fas fa-wrench" style="font-size : 6em;color:#00979c;"></i>
          <br/>
        <span style="color:#00979c">{{Configuration}}></span>
      </div>
      </div>

    <input class="form-control" placeholder="{{Rechercher}}" id="in_searchEqlogic" />


    <legend><i class="fas fa-home" id="butCol"></i>  {{Mes Equipements}}</legend>
    <div class="eqLogicThumbnailContainer">
      <?php
      foreach ($eqLogics as $eqLogic) {
        $opacity = ($eqLogic->getIsEnable()) ? '' : jeedom::getConfiguration('eqLogic:style:noactive');
        echo '<div class="eqLogicDisplayCard cursor" data-eqLogic_id="' . $eqLogic->getId() . '" style="background-color : #ffffff ; height : 200px;margin-bottom : 10px;padding : 5px;border-radius: 2px;width : 160px;margin-left : 10px;' . $opacity . '" >';
        echo "<center>";
        echo '<img src="plugins/ayat/plugin_info/ayat_icon.png" height="105" width="95" />';
        echo "</center>";
        echo '<span style="font-size : 1.1em;position:relative; top : 15px;word-break: break-all;white-space: pre-wrap;word-wrap: break-word;"><center>' . $eqLogic->getHumanName(true, true) . '</center></span>';
        echo '</div>';
      }
      ?>
    </div>
  </div>

    <div class="col-lg-10 col-md-9 col-sm-8 eqLogic" style="border-left: solid 1px #EEE; padding-left: 25px;display: none;">
        <a class="btn btn-success eqLogicAction pull-right" data-action="save"><i class="fas fa-check-circle"></i> {{Sauvegarder}}</a>
        <a class="btn btn-danger eqLogicAction pull-right" data-action="remove"><i class="fas fa-minus-circle"></i> {{Supprimer}}</a>
        <a class="btn btn-default eqLogicAction pull-right" data-action="configure"><i class="fas fa-cogs"></i> {{Configuration avancée}}</a>
        <ul class="nav nav-tabs" role="tablist">
            <li role="presentation"><a href="#" class="eqLogicAction" aria-controls="home" role="tab" data-toggle="tab" data-action="returnToThumbnailDisplay"><i class="fas fa-arrow-circle-left"></i></a></li>
            <li role="presentation" class="active"><a href="#eqlogictab" aria-controls="home" role="tab" data-toggle="tab"><i class="fas fa-tachometer"></i> {{Equipement}}</a></li>
            <li role="presentation"><a href="#commandtab" aria-controls="profile" role="tab" data-toggle="tab"><i class="fas fa-list-alt"></i> {{Commandes}}</a></li>
        </ul>
        <div class="tab-content" style="height:calc(100% - 50px);overflow:auto;overflow-x: hidden;">
            <div role="tabpanel" class="tab-pane active" id="eqlogictab">
                <form class="form-horizontal">
                    <fieldset>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Nom du Ayat}}</label>
                            <div class="col-sm-3">
                                <input type="text" class="eqLogicAttr form-control" data-l1key="id" style="display : none;" />
                                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement ayat}}"/>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" >{{Objet parent}}</label>
                            <div class="col-sm-3">
                                <select class="form-control eqLogicAttr" data-l1key="object_id">
                                    <option value="">{{Aucun}}</option>
                                    <?php
                                    foreach (jeeObject::all() as $object) {
                                        echo '<option value="' . $object->getId() . '">' . $object->getName() . '</option>';
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Catégorie}}</label>
                            <div class="col-sm-8">
                                <?php
                                foreach (jeedom::getConfiguration('eqLogic:category') as $key => $value) {
                                    echo '<label class="checkbox-inline">';
                                    echo '<input type="checkbox" class="eqLogicAttr" data-l1key="category" data-l2key="' . $key . '" />' . $value['name'];
                                    echo '</label>';
                                }
                                ?>

                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" ></label>
                            <div class="col-sm-8">
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{URL du MP3 Original}}</label>
                            <div class="col-sm-3">
                                <span class="eqLogicAttr" data-l1key="configuration" data-l2key="url"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{URL du MP3 Fr}}</label>
                            <div class="col-sm-3">
                                <span class="eqLogicAttr" data-l1key="configuration" data-l2key="urlfr"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Récitateur}}</label>
                            <div class="col-sm-3">
                                <select class="form-control eqLogicAttr" data-l1key="configuration" data-l2key="recite">
                                    <option value="abdullahbasfar">{{Abdullah Basfar}}</option>
                                    <option value="abdurrahmaansudais">{{Abdurrahmaan As-Sudais}}</option>
                                    <option value="abdulsamad">{{Abdul Samad}}</option>
                                    <option value="shaatree">{{Abu Bakr Ash-Shaatree}}</option>
                                    <option value="ahmedajamy">{{Ahmed ibn Ali al-Ajamy}}</option>
                                    <option value="alafasy">{{Alafasy}}</option>
                                    <option value="hanirifai">{{Hani Rifai}}</option>
                                    <option value="husary">{{Husary}}</option>
                                    <option value="husarymujawwad">{{Husary (Mujawwad)}}</option>
                                    <option value="hudhaify">{{Hudhaify}}</option>
                                    <option value="ibrahimakhbar">{{Ibrahim Akhdar}}</option>
                                    <option value="mahermuaiqly">{{Maher Al Muaiqly}}</option>
                                    <option value="muhammadayyoub">{{Muhammad Ayyoub}}</option>
                                    <option value="muhammadjibreel">{{Muhammad Jibreel}}</option>
                                    <option value="saoodshuraym">{{Saood bin Ibraaheem Ash-Shuraym}}</option>
                                    <option value="parhizgar">{{Parhizgar}}</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label">{{Commande Notification}}</label>
                            <div class="col-sm-3">
                                <div class="input-group">
                                    <input type="text"  class="eqLogicAttr configuration form-control" data-l1key="configuration" data-l2key="play" />
                                    <span class="input-group-btn">
                                        <a class="btn btn-default cursor" title="Rechercher une commande" id="bt_selectMailCmd"><i class="fas fa-list-alt"></i></a>
                                    </span>
                                </div>
                            </div>
                        </div>


                    </fieldset>
                </form>
            </div>
            <div role="tabpanel" class="tab-pane" id="commandtab">

                <table id="table_cmd" class="table table-bordered table-condensed">
                    <thead>
                        <tr>
                            <th style="width: 50px;">#</th>
                            <th style="width: 300px;">{{Nom}}</th>
                            <th style="width: 160px;">{{Type}}</th>
                            <th style="width: 200px;">{{Valeur}}</th>
                            <th style="width: 100px;">{{Options}}</th>
                            <th style="width: 100px;"></th>
                        </tr>
                    </thead>
                    <tbody>

                    </tbody>
                </table>

            </div>
        </div>
    </div>
</div>

<?php include_file('desktop', 'ayat', 'js', 'ayat'); ?>
<?php include_file('core', 'plugin.template', 'js'); ?>
