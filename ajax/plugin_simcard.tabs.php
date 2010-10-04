<?php
/*
 * @version $Id: computer.tabs.php 7152 2008-07-29 12:27:18Z jmd $
 -------------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org
 -------------------------------------------------------------------------

 LICENSE

 This file is part of GLPI.

 GLPI is free software; you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation; either version 2 of the License, or
 (at your option) any later version.

 GLPI is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with GLPI; if not, write to the Free Software
 Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 --------------------------------------------------------------------------
 */

// ----------------------------------------------------------------------
// Original Author of file: El Sendero S.A.
// Purpose of file:
// ----------------------------------------------------------------------

$NEEDED_ITEMS=array("computer","printer","networking","monitor","software","peripheral","phone","tracking","document","user","enterprise","contract","infocom","group");

define('GLPI_ROOT', '../../..');
include (GLPI_ROOT . "/inc/includes.php");
header("Content-Type: text/html; charset=UTF-8");
header_nocache();

useplugin('simcard',true);
		
if(!isset($_POST["ID"])) {
	exit();
}
if(!isset($_POST["sort"])) $_POST["sort"] = "";
if(!isset($_POST["order"])) $_POST["order"] = "";
if(!isset($_POST["withtemplate"])) $_POST["withtemplate"] = "";

	plugin_simcard_checkRight("simcard","r");

	if (empty($_POST["ID"])){
		switch($_POST['glpi_tab']){
			default :
				break;
		}
	}else{
		
		switch($_POST['glpi_tab']){
			
			case -1 :
				
				//show items linking form from All
				plugin_simcard_showDevice($_POST["ID"]);
				showDocumentAssociated(PLUGIN_SIMCARD_TYPE,$_POST["ID"],$_POST["withtemplate"]);
				displayPluginAction(PLUGIN_SIMCARD_TYPE,$_POST["ID"],$_POST['glpi_tab']);
				break;
			case 6:
				showJobListForItem(PLUGIN_SIMCARD_TYPE,$_POST["ID"]);
				//plugin_simcard_showTickets($_POST["ID"]);
				break;
			case 8 :
				showContractAssociated(PLUGIN_SIMCARD_TYPE,$_POST["ID"],$_POST["withtemplate"]);
				break;
			case 9 :
				//show documents linking form
				showDocumentAssociated(PLUGIN_SIMCARD_TYPE,$_POST["ID"],$_POST["withtemplate"]);
				break;
			case 10 :
				//show notes form
				showNotesForm($_POST['target'],PLUGIN_SIMCARD_TYPE,$_POST["ID"]);
				break;
			case 12 :
				//show history form
				showHistory(PLUGIN_SIMCARD_TYPE,$_POST["ID"]);
				break;
			default :
				//show items linking form
				if (!displayPluginAction(PLUGIN_SIMCARD_TYPE,$_POST["ID"],$_POST['glpi_tab'])) {
					plugin_simcard_showDevice($_POST["ID"]);
				}
				break;
		}
		ajaxFooter();
	}

?>