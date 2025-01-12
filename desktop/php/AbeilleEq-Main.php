<!-- This file displays main equipment infos.
     Included by 'AbeilleEq.php' -->

<form class="form-horizontal">
    <fieldset>

        <br>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Nom de l'équipement}}</label>
            <div class="col-sm-3">
                <input type="text" class="eqLogicAttr form-control" data-l1key="name" placeholder="{{Nom de l'équipement}}"   />
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Objet parent}}</label>
            <div class="col-sm-3">
                <select class="eqLogicAttr form-control" data-l1key="object_id">
                    <option value="">{{Aucun}}</option>
                     <?php
						foreach ((jeeObject::buildTree(null, false)) as $object) {
                            $decay = $object->getConfiguration('parentNumber');
                            // if ($object->getId() == $eqLogic->getObject()->getId())
                            //     echo '<option value="'.$object->getId().'" selected>'.str_repeat('&nbsp;&nbsp;', $decay).$object->getName().'</option>';
                            // else
							    echo '<option value="'.$object->getId().'">'.str_repeat('&nbsp;&nbsp;', $decay).$object->getName().'</option>';
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
            <label class="col-sm-3 control-label">.</label>
            <div class="col-sm-3">
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isEnable" checked/>{{Activer}}</label>
                <label class="checkbox-inline"><input type="checkbox" class="eqLogicAttr" data-l1key="isVisible" checked/>{{Visible}}</label>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Id}}</label>
            <div class="col-sm-3">
                <span class="eqLogicAttr" data-l1key="id"></span>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Time Out (min)}}</label>
            <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="timeout" placeholder="{{En minutes}}"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Note}}</label>
            <div class="col-sm-3">
                <textarea class="eqLogicAttr form-control" data-l1key="configuration" data-l2key="note" placeholder="{{Vos notes pour vous souvenir}}">Votre note</textarea>
            </div>
        </div>

        <hr>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Icone}}</label>
            <div class="col-sm-3">
                <select id="sel_icon" class="form-control eqLogicAttr" data-l1key="configuration" data-l2key="icone">
                    <option value="Abeille">{{Abeille}}</option>
                    <option value="Ruche">{{Ruche}}</option>
                    <?php
                        require_once __DIR__.'/../../core/class/AbeilleTools.class.php';
                        $items = AbeilleTools::getDeviceNameFromJson('Abeille');

                        $selectBox = array();
                        foreach ($items as $item) {
                            $device = AbeilleTools::getDeviceConfig($item, 'Abeille', 2);
                            if (!isset($device['configuration']))
                                continue; // No 'configuration' in this JSON, so no icon defined
                            if (!isset($device['configuration']['icon']))
                                continue; // No icon defined
                            if (!isset($device['type']))
                                continue; // No type defined
                            $icon = $device['configuration']['icon'];
                            $name = $device['type'];
                            $selectBox[ucwords($name)] = $icon;
                        }
                        ksort($selectBox);
                        foreach ($selectBox as $key => $value) {
                            echo "<option value=\"".$value."\">{{".$key."}}</option>";
                        }
                    ?>
                </select>
            </div>
        </div>
        <style>
           .widthSet {
                max-width: 160px;
                width:auto;
            }
        </style>
        <div class="form-group">
            <div class="col-sm-3">
            </div>
            <div class="col-sm-3" style="text-align: center">
                <!-- <img name="icon_visu" src="" width="160" height="200"/> -->
                <img name="icon_visu" class="widthSet">
            </div>
        </div>

        <hr>
        <div class="form-group" >
            <label class="col-sm-3 control-label">{{Source d'alimentation}}</label>
            <div class="col-sm-3">
                <?php
                /* If battery powered eq. 'batteryType' is defined in device JSON file */
                if ($eqLogic->getConfiguration('battery_type', '') != "") {
                    echo '<span>{{Batterie}}  </span>';
                    echo '<input class="eqLogicAttr" data-l1key="configuration" data-l2key="battery_type" />';
                } else
                    echo '<span>{{Secteur}}</span>';
                ?>
        </div>
        </div>

        <hr>
        <div class="form-group">
            <label class="col-sm-3 control-label">{{Position pour les representations graphiques.}}</label>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Position X}}</label>
            <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration"
                data-l2key="positionX"
                placeholder="{{Position sur l axe horizontal (0 à gauche - 1000 à droite)}}"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Position Y}}</label>
            <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration"
                data-l2key="positionY"
                placeholder="{{Position sur l axe vertical (0 en haut - 1000 en bas)}}"/>
            </div>
        </div>

        <div class="form-group">
            <label class="col-sm-3 control-label">{{Position Z}}</label>
            <div class="col-sm-3">
                <input class="eqLogicAttr form-control" data-l1key="configuration"
                data-l2key="positionZ"
                placeholder="{{Position en hauteur (0 en bas - 1000 en haut)}}"/>
            </div>
        </div>

    </fieldset>
</form>
