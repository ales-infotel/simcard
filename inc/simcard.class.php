<?php
/*
 * @version $Id$
 LICENSE

  This file is part of the simcard plugin.

 Order plugin is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 Order plugin is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; along with Simcard. If not, see <http://www.gnu.org/licenses/>.
 --------------------------------------------------------------------------
 @package   simcard
 @author    the simcard plugin team
 @copyright Copyright (c) 2010-2011 Simcard plugin team
 @license   GPLv2+
            http://www.gnu.org/licenses/gpl.txt
 @link      https://github.com/pluginsglpi/simcard
 @link      http://www.glpi-project.org/
 @since     2009
 ---------------------------------------------------------------------- */

if (!defined('GLPI_ROOT')) {
   die("Sorry. You can't access directly to this file");
}

/// Class Simcard
class PluginSimcardSimcard extends CommonDBTM {

   // From CommonDBTM
   //static $types = array('');
  public $dohistory = true;
  
  static $rightname = PluginSimcardProfile::RIGHT_SIMCARD_SIMCARD;
  protected $usenotepad            = true;
  
  //~ static $types = array('Computer', 'Monitor', 'NetworkEquipment', 'Peripheral', 'Phone', 'Printer', 'Software', 'Entity');
  static $types = array('Phone' , 'Entity');
  
   /**
    * Name of the type
    *
    * @param $nb  integer  number of item in the type (default 0)
   **/
   static function getTypeName($nb=0) {
      global $LANG;
      return _n('SIM card', 'SIM cards', $nb, 'simcard');
   }

   /**
    * @since version 0.85
    *
    * @see commonDBTM::getRights()
    **/
   function getRights($interface='central') {
      $rights = parent::getRights();
      $rights[PluginSimcardProfile::SIMCARD_ASSOCIATE_TICKET] = __('Associable to a ticket');
     
     return $rights;
   }

   function defineTabs($options=array()) {
      global $LANG;
      $ong     = array();
      $this->addDefaultFormTab($ong);
      if ($this->fields['id'] > 0) {
         if (!isset($options['withtemplate']) || empty($options['withtemplate'])) {
            $this->addStandardTab('PluginSimcardSimcard_Item', $ong, $options);
            $this->addStandardTab('NetworkPort', $ong, $options);
            $this->addStandardTab('Document_Item',$ong,$options);
            $this->addStandardTab('Infocom',$ong,$options);
            $this->addStandardTab('Contract_Item', $ong, $options);
            if ($this->fields['is_helpdesk_visible'] == 1) {
               $this->addStandardTab('Ticket',$ong,$options);
               $this->addStandardTab('Item_Problem', $ong, $options);
            }
            $this->addStandardTab('Notepad',$ong,$options);
            $this->addStandardTab('Log',$ong,$options);
            $this->addStandardTab('Event',$ong,$options);
         } else {
            $this->addStandardTab('Infocom',$ong,$options);
            $this->addStandardTab('Contract_Item', $ong, $options);
            $this->addStandardTab('Document_Item',$ong,$options);
            $this->addStandardTab('Log',$ong,$options);
            $this->addStandardTab('Event',$ong,$options);
         }
      } else {
         $ong[1] = __s('Main');
      }

      return $ong;
   }

   /**
    * Print the simcard form
    *
    * @param $ID        integer ID of the item
    * @param $options   array
    *     - target for the Form
    *     - withtemplate template or basic simcard
    *
    *@return Nothing (display)
   **/
    function showForm($ID, $options=array()) {
      global $CFG_GLPI, $DB, $LANG;

      if (!$this->canView()) return false;
      
      $target       = $this->getFormURL();
      $withtemplate = '';

      if (isset($options['target'])) {
        $target = $options['target'];
      }

      if (isset($options['withtemplate'])) {
         $withtemplate = $options['withtemplate'];
      }

      $this->showFormHeader($options);

      if (isset($options['itemtype']) && isset($options['items_id'])) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>".__s('Associated element')."</td>";
         echo "<td>";
         $item = new $options['itemtype'];
         $item->getFromDB($options['items_id']);
         echo $item->getLink(1);
         echo "</td>";
         echo "<td colspan='2'></td></tr>\n";
         echo "<input type='hidden' name='_itemtype' value='".$options['itemtype']."'>";
         echo "<input type='hidden' name='_items_id' value='".$options['items_id']."'>";
      }
      
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__s('Name').
                          (isset($options['withtemplate']) && $options['withtemplate']?"*":"").
           "</td>";
      echo "<td>";
      $objectName = autoName($this->fields["name"], "name",
                             (isset($options['withtemplate']) && $options['withtemplate']==2),
                             $this->getType(), $this->fields["entities_id"]);
      Html::autocompletionTextField($this, 'name', array('value' => $objectName));
      echo "</td>";
      echo "<td>".__s('Status')."</td>";
      echo "<td>";
      Dropdown::show('State', array('value' => $this->fields["states_id"]));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__s('Location')."</td>";
      echo "<td>";
      Dropdown::show('Location', array('value'  => $this->fields["locations_id"],
                                       'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "<td>".__s('Type of SIM card', 'simcard')."</td>";
      echo "<td>";
      Dropdown::show('PluginSimcardSimcardType',
                     array('value' => $this->fields["plugin_simcard_simcardtypes_id"]));
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__s('Technician in charge of the hardware')."</td>";
      echo "<td>";
      User::dropdown(array('name'   => 'users_id_tech',
                           'value'  => $this->fields["users_id_tech"],
                           'right'  => 'interface',
                           'entity' => $this->fields["entities_id"]));
      echo "</td>";
      echo "<td>".__s('Size', 'simcard')."</td>";
      echo "<td>";
      Dropdown::show('PluginSimcardSimcardSize',
                     array('value' => $this->fields["plugin_simcard_simcardsizes_id"]));
      echo "</td></tr>\n";

//       TODO : Add group in charge of hardware      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__s('Group in charge of the hardware')."</td>";
      echo "<td>";
      Group::dropdown(array('name'      => 'groups_id_tech',
      'value'     => $this->fields['groups_id_tech'],
      'entity'    => $this->fields['entities_id'],
      'condition' => ['is_assign' => 1]));
      echo "</td>";
      
      echo "<td>".__s('Voltage', 'simcard')."</td>";
      echo "<td>";
      Dropdown::show('PluginSimcardSimcardVoltage',
                     array('value' => $this->fields["plugin_simcard_simcardvoltages_id"]));
      echo "</td></tr>\n";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__s('Provider', 'simcard')."</td>";
      echo "<td>";
      Dropdown::show('PluginSimcardPhoneOperator',
                     array('value' => $this->fields["plugin_simcard_phoneoperators_id"]));
      echo "</td>";

      echo "<td>" . __s('Associable items to a ticket') . "&nbsp;:</td><td>";
      Dropdown::showYesNo('is_helpdesk_visible',$this->fields['is_helpdesk_visible']);
      echo "</td></tr>\n";
   
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__s('User')."</td>";
      echo "<td>";
      User::dropdown(array('value'  => $this->fields["users_id"],
                           'entity' => $this->fields["entities_id"],
                           'right'  => 'all'));
      echo "</td>";

      echo "<input type='hidden' name='is_global' value='1'>";
      
      echo "<td>".__s("Inventory number").
                          (isset($options['withtemplate']) && $options['withtemplate']?"*":"").
           "</td>";
      echo "<td>";
      $objectName = autoName($this->fields["otherserial"], "otherserial",
                             (isset($options['withtemplate']) && $options['withtemplate']==2),
                             $this->getType(), $this->fields["entities_id"]);
      Html::autocompletionTextField($this, 'otherserial', array('value' => $objectName));
      echo "</td></tr>\n";
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__s('Group')."</td>";
      echo "<td>";
      Dropdown::show('Group', array('value'     => $this->fields["groups_id"],
                                    'entity'    => $this->fields["entities_id"]));

      echo "</td></tr>\n";
      
      echo "<tr class='tab_bg_1'>";
      echo "<td>".__s('Phone number', 'simcard')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,'phonenumber');
      echo "</td>";
      echo "<td rowspan='6'>".__s('Comments')."</td>";
      echo "<td rowspan='6' class='middle'>";
      echo "<textarea cols='45' rows='15' name='comment' >".$this->fields["comment"]."</textarea>";
      echo "</td></tr>\n";

      echo "<tr class='tab_bg_1'>";
      echo "<td>".__s('IMSI', 'simcard')."</td>";
      echo "<td>";
      Html::autocompletionTextField($this,'serial');
      echo "</td></tr>\n";
      
      //Only show PIN and PUK code to users who can write (theses informations are highly sensible)
      if (PluginSimcardSimcard::canUpdate()) {
         echo "<tr class='tab_bg_1'>";
         echo "<td>".__s('Pin 1', 'simcard')."</td>";
         echo "<td>";
         Html::autocompletionTextField($this,'pin');
         echo "</td></tr>\n";
         
         echo "<tr class='tab_bg_1'>";
         echo "<td>".__s('Pin 2', 'simcard')."</td>";
         echo "<td>";
         Html::autocompletionTextField($this,'pin2');
         echo "</td></tr>\n";
         
         echo "<tr class='tab_bg_1'>";
         echo "<td>".__s('Puk 1', 'simcard')."</td>";
         echo "<td>";
         Html::autocompletionTextField($this,'puk');
         echo "</td></tr>\n";

         echo "<tr class='tab_bg_1'>";
         echo "<td>".__s('Puk 2', 'simcard')."</td>";
         echo "<td>";
         Html::autocompletionTextField($this,'puk2');
         echo "</td></tr>\n";
      }

      $this->showFormButtons($options);
      //$this->addDivForTabs();

      return true;
   }

   function prepareInputForAdd($input) {

      if (isset($input["id"]) && $input["id"]>0) {
         $input["_oldID"] = $input["id"];
      }
      unset($input['id']);
      unset($input['withtemplate']);

      return $input;
   }
   
   function post_addItem() {
      global $DB, $CFG_GLPI;

      // Manage add from template
      if (isset($this->input["_oldID"])) {
         Infocom::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
         Contract_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);
         Document_Item::cloneItem($this->getType(), $this->input["_oldID"], $this->fields['id']);          
      }
   
      if (isset($this->input['_itemtype']) && isset($this->input['_items_id'])) {
         $simcard_item = new PluginSimcardSimcard_Item();
         $tmp['plugin_simcard_simcards_id'] = $this->getID();
         $tmp['itemtype'] = $this->input['_itemtype'];
         $tmp['items_id'] = $this->input['_items_id'];
         $simcard_item->add($tmp);
      }
      
   }
   
    function rawSearchOptions() {
      global $CFG_GLPI, $LANG;

      $tab = array();
//      $tab['common']             = __s('SIM card', 'simcard');

       $tab[] = [
          'id'   => 'common',
          'name' => self::getTypeName(2)
       ];

       $tab[] = [
          'id'            => '1',
          'table'         => $this->getTable(),
          'field'         => 'name',
          'name'          => __('Name'),
          'datatype'      => 'itemlink',
          'itemlink_type' => $this->getType(),
       ];
      
//      $tab[2]['table']           = $this->getTable();
//      $tab[2]['field']           = 'id';
//      $tab[2]['name']            = __('ID');
//      $tab[2]['massiveaction']   = false; // implicit field is id
//      $tab[2]['injectable']      = false;
      
      $tab[4]['table']           = 'glpi_plugin_simcard_simcardtypes';
      $tab[4]['id']           = '4';
      $tab[4]['field']           = 'name';
      $tab[4]['name']            = __('Type');
      $tab[4]['datatype']        = 'dropdown';
      $tab[4]['massiveaction']   = true;
      $tab[4]['checktype']       = 'text';
      $tab[4]['displaytype']     = 'dropdown';
      $tab[4]['injectable']      = true;

      $tab[5]['id']           = '5';
      $tab[5]['table']           = $this->getTable();
      $tab[5]['field']           = 'serial';
      $tab[5]['name']            = __('IMSI', 'simcard');
      $tab[5]['datatype']        = 'string';
      $tab[5]['checktype']       = 'text';
      $tab[5]['displaytype']     = 'text';
      $tab[5]['injectable']      = true;

      $tab[6]['id']           = '6';
      $tab[6]['table']           = $this->getTable();
      $tab[6]['field']           = 'otherserial';
      $tab[6]['name']            = __('Inventory number');
      $tab[6]['datatype']        = 'string';
      $tab[6]['checktype']       = 'text';
      $tab[6]['displaytype']     = 'text';
      $tab[6]['injectable']      = true;

      $tab[16]['id']           = '16';
      $tab[16]['table']          = $this->getTable();
      $tab[16]['field']          = 'comment';
      $tab[16]['name']           = __('Comments');
      $tab[16]['datatype']       = 'text';
      $tab[16]['linkfield']      = 'comment';
      $tab[16]['checktype']      = 'text';
      $tab[16]['displaytype']    = 'multiline_text';
      $tab[16]['injectable']     = true;
      
      $tab += Location::getSearchOptionsToAdd();
      $tab += Notepad::getSearchOptionsToAdd();

      $tab[3]['checktype']       = 'text';
      $tab[3]['displaytype']     = 'dropdown';
      $tab[3]['injectable']      = true;
      
      $tab[91]['injectable']     = false;
      $tab[93]['injectable']     = false;

      $tab[19]['table']          = $this->getTable();
      $tab[19]['field']          = 'date_mod';
      $tab[19]['name']           = __('Last update');
      $tab[19]['datatype']       = 'datetime';
      $tab[19]['massiveaction']  = false;
      $tab[19]['injectable']     = false;
      
      // TODO : This index has not any similar in GLPI, should find an other index
      $tab[23]['table']          = 'glpi_plugin_simcard_simcardvoltages';
      $tab[23]['field']          = 'name';
      $tab[23]['name']           = __('Voltage', 'simcard');
      $tab[23]['datatype']       = 'dropdown';
      $tab[23]['checktype']      = 'text';
      $tab[23]['displaytype']    = 'dropdown';
      $tab[23]['injectable']     = true;
      
      $tab[24]['table']          = 'glpi_users';
      $tab[24]['field']          = 'name';
      $tab[24]['linkfield']      = 'users_id_tech';
      $tab[24]['name']           = __('Technician in charge of the hardware');
      $tab[24]['datatype']       = 'dropdown';
      $tab[24]['right']          = 'own_ticket';
      $tab[24]['checktype']      = 'text';
      $tab[24]['displaytype']    = 'dropdown';
      $tab[24]['injectable']     = true;

      $tab[25]['table']          = 'glpi_plugin_simcard_simcardsizes';
      $tab[25]['field']          = 'name';
      $tab[25]['name']           = __('Size', 'simcard');
      $tab[25]['datatype']       = 'dropdown';
      $tab[25]['checktype']      = 'text';
      $tab[25]['displaytype']    = 'dropdown';
      $tab[25]['injectable']     = true;
      
      $tab[26]['table']          = 'glpi_plugin_simcard_phoneoperators';
      $tab[26]['field']          = 'name';
      $tab[26]['name']           = __('Provider', 'simcard');
      $tab[26]['datatype']       = 'dropdown';
      $tab[26]['checktype']      = 'text';
      $tab[26]['displaytype']    = 'dropdown';
      $tab[26]['injectable']     = true;
      
      $tab[27]['table']          = $this->getTable();
      $tab[27]['field']          = 'phonenumber';
      $tab[27]['name']           = __('Phone number', 'simcard');
      $tab[27]['datatype']       = 'string';
      $tab[27]['checktype']      = 'text';
      $tab[27]['displaytype']    = 'text';
      $tab[27]['injectable']     = true;
      
      if (PluginSimcardSimcard::canUpdate()) {
         $tab[28]['table']       = $this->getTable();
         $tab[28]['field']       = 'pin';
         $tab[28]['name']        = __('Pin 1', 'simcard');
         $tab[28]['datatype']    = 'string';
         $tab[28]['checktype']   = 'text';
         $tab[28]['displaytype'] = 'text';
         $tab[28]['injectable']  = true;
         
         $tab[29]['table']       = $this->getTable();
         $tab[29]['field']       = 'puk';
         $tab[29]['name']        = __('Puk 1', 'simcard');
         $tab[29]['datatype']    = 'string';
         $tab[29]['checktype']   = 'text';
         $tab[29]['displaytype'] = 'text';
         $tab[29]['injectable']  = true;

         $tab[30]['table']       = $this->getTable();
         $tab[30]['field']       = 'pin2';
         $tab[30]['name']        = __('Pin 2', 'simcard');
         $tab[30]['datatype']    = 'string';
         $tab[30]['checktype']   = 'text';
         $tab[30]['displaytype'] = 'text';
         $tab[30]['injectable']  = true;
         
         $tab[32]['table']       = $this->getTable();
         $tab[32]['field']       = 'puk2';
         $tab[32]['name']        = __('Puk 2', 'simcard');
         $tab[32]['datatype']    = 'string';
         $tab[32]['checktype']   = 'text';
         $tab[32]['displaytype'] = 'text';
         $tab[32]['injectable']  = true;
      }

      $tab[31]['table']          = 'glpi_states';
      $tab[31]['field']          = 'name';
      $tab[31]['name']           = __('Status');
      $tab[31]['datatype']       = 'dropdown';
      $tab[31]['checktype']      = 'text';
      $tab[31]['displaytype']    = 'dropdown';
      $tab[31]['injectable']     = true;
      
      $tab[71]['table']          = 'glpi_groups';
      $tab[71]['field']          = 'completename';
      $tab[71]['name']           = __('Group');
      $tab[71]['datatype']       = 'dropdown';
      $tab[71]['checktype']      = 'text';
      $tab[71]['displaytype']    = 'dropdown';
      $tab[71]['injectable']     = true;
      
      $tab[49]['table']          = 'glpi_groups';
      $tab[49]['field']          = 'name';
      $tab[49]['linkfield']      = 'groups_id_tech';
      $tab[49]['condition']      = '`is_assign`';
      $tab[49]['name']           = __('Group in charge of the hardware');
      $tab[49]['datatype']       = 'dropdown';
      $tab[49]['checktype']      = 'text';
      $tab[49]['displaytype']    = 'dropdown';
      $tab[49]['injectable']     = true;
     
      $tab[70]['table']          = 'glpi_users';
      $tab[70]['field']          = 'name';
      $tab[70]['name']           = __('User');
      $tab[70]['datatype']       = 'dropdown';
      $tab[70]['right']          = 'all';
      $tab[70]['checktype']      = 'text';
      $tab[70]['displaytype']    = 'dropdown';
      $tab[70]['injectable']     = true;
      
      $tab[80]['table']          = 'glpi_entities';
      $tab[80]['field']          = 'completename';
      $tab[80]['name']           = __('Entity');
      $tab[80]['injectable']     = false;
      
      $tab[90]['table']          = $this->getTable();
      $tab[90]['field']          = 'notepad';
      $tab[90]['name']           = __('Notes');
      $tab[90]['massiveaction']  = false;
      $tab[90]['linkfield']      = 'notepad';
      $tab[90]['checktype']      = 'text';
      $tab[90]['displaytype']    = 'multiline_text';
      $tab[90]['injectable']     = false;

      foreach ($tab as $id => $t) {
         if(!isset($t['id'])) {
            $tab[$id]['id'] = $id;
         }
      }
      return $tab;
   }
   
   /**
    * Installation of the itemtype
    * 
    * @param Migration $migration migration helper instance
    */
   static function install(Migration $migration) {
      global $DB;
      $table = getTableForItemType(__CLASS__);
      if (!$DB->tableExists($table)) {
         $query = "CREATE TABLE IF NOT EXISTS `$table` (
              `id` int(11) NOT NULL AUTO_INCREMENT,
              `entities_id` int(11) NOT NULL DEFAULT '0',
              `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
              `phonenumber` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
              `serial` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
              `pin` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
              `pin2` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
              `puk` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
              `puk2` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
              `otherserial` varchar(255) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL DEFAULT '',
              `states_id` int(11) NOT NULL DEFAULT '0',
              `locations_id` int(11) NOT NULL DEFAULT '0',
              `users_id` int(11) NOT NULL DEFAULT '0',
              `users_id_tech` int(11) NOT NULL DEFAULT '0',
              `groups_id` int(11) NOT NULL DEFAULT '0',
              `groups_id_tech` int(11) NOT NULL DEFAULT '0',
              `plugin_simcard_phoneoperators_id` int(11) NOT NULL DEFAULT '0',
              `manufacturers_id` int(11) NOT NULL DEFAULT '0',
              `plugin_simcard_simcardsizes_id` int(11) NOT NULL DEFAULT '0',
              `plugin_simcard_simcardvoltages_id` int(11) NOT NULL DEFAULT '0',
              `plugin_simcard_simcardtypes_id` int(11) NOT NULL DEFAULT '0',
              `comment` text CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL,
              `date_mod` datetime DEFAULT NULL,
              `is_template` tinyint(1) NOT NULL DEFAULT '0',
              `is_global` tinyint(1) NOT NULL DEFAULT '0',
              `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
              `template_name` varchar(255) COLLATE utf8_unicode_ci NULL,
              `ticket_tco` decimal(20,4) DEFAULT '0.0000',
              `is_helpdesk_visible` tinyint(1) NOT NULL DEFAULT '1',
              PRIMARY KEY (`id`),
              KEY `name` (`name`),
              KEY `entities_id` (`entities_id`),
              KEY `states_id` (`states_id`),
              KEY `plugin_simcard_phoneoperators_id` (`plugin_simcard_phoneoperators_id`),
              KEY `plugin_simcard_simcardsizes_id` (`plugin_simcard_simcardsizes_id`),
              KEY `plugin_simcard_simcardvoltages_id` (`plugin_simcard_simcardvoltages_id`),
              KEY `manufacturers_id` (`manufacturers_id`),
              KEY `pin` (`pin`),
              KEY `pin2` (`pin2`),
              KEY `puk` (`puk`),
              KEY `puk2` (`puk2`),
              KEY `serial` (`serial`),
              KEY `users_id` (`users_id`),
              KEY `users_id_tech` (`users_id_tech`),
              KEY `groups_id` (`groups_id`),
              KEY `is_template` (`is_template`),
              KEY `is_deleted` (`is_deleted`),
              KEY `is_helpdesk_visible` (`is_helpdesk_visible`),
              KEY `is_global` (`is_global`)
            ) ENGINE=InnoDB  DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci ;";
         $DB->query($query) or die("Error adding table $table");
      }
   }
   
   static function upgrade(Migration $migration) {
      global $DB;
      
      switch (plugin_simcard_currentVersion()) {
      	 case '1.2':
      	    $sql = "ALTER TABLE `glpi_plugin_simcard_simcards`
                    ADD `plugin_simcard_simcardtypes_id` int(11) NOT NULL DEFAULT '0' AFTER `plugin_simcard_simcardvoltages_id`,
      	            ADD `groups_id_tech` int(11) NOT NULL DEFAULT '0' AFTER `groups_id`";
      	     
      	    $DB->query($sql) or die($DB->error());
      	    break;

      	 case '1.3':
      	 case '1.3.1':
      	 case '1.4':
      	 	// Migrate notepad data
      	 	if (FieldExists('glpi_plugin_simcard_simcards', 'notepad')) {
      	 		$query = "SELECT id, notepad
      	 		FROM `glpi_plugin_simcard_simcards`
      	 		WHERE notepad IS NOT NULL
      	 		AND notepad <> ''";
      	 		foreach ($DB->request($query) as $data) {
      	 			$iq = "INSERT INTO `glpi_notepads`
                             (`itemtype`, `items_id`, `content`, `date`, `date_mod`)
                      VALUES ('".getItemTypeForTable('glpi_plugin_simcard_simcards')."', '".$data['id']."',
                              '".addslashes($data['notepad'])."', NOW(), NOW())";
      	 			$DB->queryOrDie($iq, "0.85 migrate notepad data");
      	 		}
      	 		$sql = "ALTER TABLE `glpi_plugin_simcard_simcards`
                    DROP `notepad`";
      	     
      	    	$DB->query($sql) or die($DB->error());
      	 	}
      	 	break;
      }
   }
   
   static function uninstall() {
      global $DB;

      // Remove unicity constraints on simcards
      FieldUnicity::deleteForItemtype("SimcardSimcard");

      //old : , 'Bookmark'
      foreach (array('Notepad', 'DisplayPreference', 'Contract_Item', 'Infocom', 'Fieldblacklist', 'Document_Item', 'Log') as $itemtype) {
         $item = new $itemtype();
         $item->deleteByCriteria(array('itemtype' => __CLASS__));
      }
      
      $plugin = new Plugin();
      if ($plugin->isActivated('datainjection') && class_exists('PluginDatainjectionModel')) {
         PluginDatainjectionModel::clean(array('itemtype' => __CLASS__));
      }

      if ($plugin->isInstalled('customfields') && $plugin->isActivated('customfields')) {
         PluginCustomfieldsItemtype::unregisterItemtype('PluginSimcardSimcard');
      }
      
      $table = getTableForItemType(__CLASS__);
      $DB->query("DROP TABLE IF EXISTS `$table`");
   }

   function getTabNameForItem(CommonGLPI $item, $withtemplate=0) {
      global $LANG;

      if (in_array(get_class($item), PluginSimcardSimcard_Item::getClasses())
         || get_class($item) == 'Profile') {
         return array(1 => _sn('SIM card', 'SIM cards', 2, 'simcard'));
      } elseif (get_class($item) == __CLASS__) {
         return _sn('SIM card', 'SIM cards', 2, 'simcard');
      }
      return '';
  }

   /**
    *  Show tab content for a simcard item
    * 
    * @param CommonGLPI $item
    * @param number $tabnum
    * @param number $withtemplate
    */
   static function displayTabContentForItem(CommonGLPI $item, $tabnum=1, $withtemplate=0) {
      
      $self=new self();
      if($item->getType()=='PluginSimcardSimcard') {
         $self->showtotal($item->getField('id'));
      }
      return true;
   }

  /**
    * Type than could be linked to a Rack
    *
    * @param $all boolean, all type, or only allowed ones
    *
    * @return array of types
   **/
   static function getTypes($all=false) {

      if ($all) {
         return self::$types;
      }

      // Only allowed types
      $types = self::$types;

      foreach ($types as $key => $type) {
         if (!class_exists($type)) {
            continue;
         }

         $item = new $type();
         if (!$item->canView()) {
            unset($types[$key]);
         }
      }
      return $types;
   }
   
   /**
    * Add menu entries the plugin needs to show
    * 
    * @return array
    */
   static function getMenuContent() {
   	global $CFG_GLPI;
   		
   	$menu = array();
      $menu['title'] = self::getTypeName(2);
      $menu['page']  = self::getSearchURL(false);
      $menu['links']['search'] = self::getSearchURL(false);
      if (self::canCreate()) {
         $menu['links']['add'] = '/front/setup.templates.php?itemtype=PluginSimcardSimcard&add=1';
         $menu['links']['template'] = '/front/setup.templates.php?itemtype=PluginSimcardSimcard&add=0';
      }
      $menu['icon']            = static::getIcon();
      return $menu;
   }
      

   /**
    * Actions done when item is deleted from the database
    *
    * @return nothing
    * */
   function cleanDBonPurge() {
      $link = new PluginSimcardSimcard_Item();
      $link->cleanDBonItemDelete($this->getType(), $this->getID());
   }

   /**
    * Delete an item in the database.
    *
    * @see CommonDBTM::delete()
    *
    * @param $input     array    the _POST vars returned by the item form when press delete
    * @param $force     boolean  force deletion (default 0)
    * @param $history   boolean  do history log ? (default 1)
    *
    * @return boolean : true on success
   **/
   function delete(array $input, $force=0, $history=1) {
      $deleteSuccessful = parent::delete($input, $force, $history);
      if ($deleteSuccessful != false) {
	      if ($force == 1) {
	      	$notepad = new Notepad();
	      	$notepad->deleteByCriteria(array(
	      	   'itemtype' => 'PluginSimcardSimcard',
	      	   'items_id' => $input['id']
	      	));
	      }
      }
      return $deleteSuccessful;
   }
   
   /**
    * @since version 0.85
    *
    * @see CommonDBTM::getSpecificMassiveActions()
    * */
   function getSpecificMassiveActions($checkitem = NULL) {
      $isadmin = static::canUpdate();
      $actions = parent::getSpecificMassiveActions($checkitem);

      if ($_SESSION['glpiactiveprofile']['interface'] == 'central') {
         if ($isadmin) {
            if (Session::haveRight('transfer', READ) && Session::isMultiEntitiesMode()) {
               $actions['PluginSimcardSimcard'.MassiveAction::CLASS_ACTION_SEPARATOR.'transfer'] = __('Transfer');
            }
         }
      }
      return $actions;
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::showMassiveActionsSubForm()
    * */
   static function showMassiveActionsSubForm(MassiveAction $ma) {

      switch ($ma->getAction()) {
         case "transfer" :
            Dropdown::show('Entity');
            echo Html::submit(_x('button', 'Post'), array('name' => 'massiveaction'));
            return true;
            break;
      }
      return parent::showMassiveActionsSubForm($ma);
   }

   /**
    * @since version 0.85
    *
    * @see CommonDBTM::processMassiveActionsForOneItemtype()
    * */
   static function processMassiveActionsForOneItemtype(MassiveAction $ma, CommonDBTM $item, array $ids) {
      global $DB;

      switch ($ma->getAction()) {
         case "transfer" :
            $input = $ma->getInput();
            if ($item->getType() == 'PluginSimcardSimcard') {
               foreach ($ids as $key) {
                  // Types
                  $item->getFromDB($key);
                  $type = PluginSimcardSimcardType::transfer($item->fields["plugin_simcard_simcardtypes_id"], $input['entities_id']);
                  if ($type > 0) {
                     $values["id"]                              = $key;
                     $values["plugin_simcard_simcardtypes_id"] = $type;
                     $item->update($values);
                  }
                  
                  // Size
                  $size = PluginSimcardSimcardSize::transfer($item->fields["plugin_simcard_simcardsizes_id"], $input['entities_id']);
                  if ($size > 0) {
                     $values["id"]                             = $key;
                     $values["plugin_simcard_simcardsizes_id"] = $size;
                     $item->update($values);
                  }
                  
                  // Voltage
                  $voltage = PluginSimcardSimcardVoltage::transfer($item->fields["plugin_simcard_simcardvoltages_id"], $input['entities_id']);
                  if ($voltage > 0) {
                     $values["id"]                                = $key;
                     $values["plugin_simcard_simcardvoltages_id"] = $voltage;
                     $item->update($values);
                  }
                  
                  // Phoneoperator
                  $phoneoperator = PluginSimcardPhoneOperator::transfer($item->fields["plugin_simcard_phoneoperators_id"], $input['entities_id']);
                  if ($phoneoperator > 0) {
                     $values["id"]                                = $key;
                     $values["plugin_simcard_phoneoperators_id"] = $phoneoperator;
                     $item->update($values);
                  }

                  unset($values);
                  $values["id"]          = $key;
                  $values["entities_id"] = $input['entities_id'];

                  if ($item->update($values)) {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_OK);
                  } else {
                     $ma->itemDone($item->getType(), $key, MassiveAction::ACTION_KO);
                  }
               }
            }
            return;
      }
      parent::processMassiveActionsForOneItemtype($ma, $item, $ids);
   }

   static function getIcon() {
      return "fas fa-sim-card";
   }

   /**
    * @return translated
    */
   static function getMenuName() {
      return self::getTypeName(2);
   }

}
?>
