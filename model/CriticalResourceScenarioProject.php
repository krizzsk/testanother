<?php 
/*** COPYRIGHT NOTICE *********************************************************
 *
 * Copyright 2009-2017 ProjeQtOr - Pascal BERNARD - support@projeqtor.org
 * Contributors : -
 *
 * This file is part of ProjeQtOr.
 * 
 * ProjeQtOr is free software: you can redistribute it and/or modify it under 
 * the terms of the GNU Affero General Public License as published by the Free 
 * Software Foundation, either version 3 of the License, or (at your option) 
 * any later version.
 * 
 * ProjeQtOr is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS 
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for 
 * more details.
 *
 * You should have received a copy of the GNU Affero General Public License along with
 * ProjeQtOr. If not, see <http://www.gnu.org/licenses/>.
 *
 * You can get complete code of ProjeQtOr, other resource, help and information
 * about contributors at http://www.projeqtor.org 
 *     
 *** DO NOT REMOVE THIS NOTICE ************************************************/

/* ============================================================================
 * Scenario for Critial Resources : specific project types
 */ 
require_once('_securityCheck.php');
class CriticalResourceScenarioProject extends SqlElement {

  // extends SqlElement, so has $id
  public $_sec_Description;
  public $id;    // redefine $id to specify its visible place 
  public $idProject;
  public $idUser;
  public $idScenario;
  public $proposale;
  public $monthDelay;
  //public $_sec_void;
  

  
   /** ==========================================================================
   * Constructor
   * @param $id the id of the object in the database (null if not stored yet)
   * @return void
   */ 
  function __construct($id = NULL, $withoutDependentObjects=false) {
    parent::__construct($id,$withoutDependentObjects);
  }

  
   /** ==========================================================================
   * Destructor
   * @return void
   */ 
  function __destruct() {
    parent::__destruct();
  }

// ============================================================================**********
// GET STATIC DATA FUNCTIONS
// ============================================================================**********

  public static function getScenarioProjectInfo(&$listProjectsType, &$listProjectsDelay, $idScenario=null) {
    $crsp=new CriticalResourceScenarioProject();
    $list=$crsp->getSqlElementsFromCriteria(array('idScenario'=>$idScenario, 'idUser'=>getCurrentUserId()));
    foreach ($list as $crsp) {
      if ($crsp->proposale==2) $listProjectsType[$crsp->idProject]='OPE';
      else if ($crsp->proposale==1) $listProjectsType[$crsp->idProject]='PRP'; 
      if ($crsp->monthDelay) $listProjectsDelay[$crsp->idProject]=$crsp->monthDelay;
    }
  }
  
  public static function drawProjectList(){
    $user=getSessionUser();
    $prjVisLst=$user->getVisibleProjects();
    $prjLst=$user->getHierarchicalViewOfVisibleProjects(true);
    $width=RequestHandler::getValue('destinationWidth',1500);
    $widthTab=$width/2-100;
    $widthType=180;
    $widthDate=300;
    $widthProj=$widthTab-$widthType-$widthDate;
    $projType=array();
    $projDelay=array();
    $showAllProject='false';
    self::getScenarioProjectInfo($projType,$projDelay,null);
    echo '<table align="center" style="margin:10px 20px">';
    echo '<tr>';
    echo '  <td class="messageHeader" colspan="" style="width:'.$widthProj.'px;border-right:0">'.i18n('colIdProject');
    echo '  <td class="messageHeader" colspan="" style="width:'.$widthType.'px;border-right:0">'.i18n('colType');
    echo '  <td class="messageHeader" colspan="" style="text-align:center;width:'.$widthDate.'px;border-right:0"><table style="width:100%"><tr>';
    echo '    <td style="width:35%">'.i18n('colStart').'</td><td style="width:35%">'.i18n('colEnd').'</td><td>'.i18n('colLate').' ('.i18n('colMonth').')&nbsp;</td>';
    echo '  </tr></table></td>';
    $parmSizeProject=null;
    $countPro=0;
    $adminProject=Project::getAdminitrativeProjectList(true);
    foreach ($prjLst as $sharpid=>$sharpName) {
      if (isset($adminProject[pq_trim($sharpid,'#')])) continue;
      $countPro++;
      if($parmSizeProject!='' and $parmSizeProject==$countPro and $showAllProject=='false'){
        echo '<tr style="text-align: center;font-weight:bold;">';
        echo '<td colspan="3"  class="messageData"><div >'.i18n('limitedDisplay', array($parmSizeProject)).'</div></td>';
        echo'</tr>';
        break;
      }
      $split=pq_explode('#', $sharpName);
      $wbs=$split[0];
      $name=pq_str_replace('&sharp;', '#', $split[1]);
      $id=pq_substr($sharpid, 1);
      $project=new Project($id,false);
      $typ=SqlList::getFieldFromId('ProjectType', $project->idProjectType, 'code');
      echo '<tr style="height:25px;position:relative;">';
      echo '<td class="reportTableData" style="width:'.$widthProj.'px;text-align:left"><div class="dataContent" style="width:'.$widthProj.'px;"><div class="dataExtend" style="min-width:'.($widthProj-5).'px">'.$wbs.' - '.$name.'&nbsp;&nbsp;&nbsp;</div></div></td>';
      $alteredType=false;
      if (isset($projType[$project->id])) {
        $alteredType=$projType[$project->id];
      }
      echo '<td id="scenarioProjectTD_'.$project->id.'" class="reportTableData'.(($alteredType)?' alteredScenario':'').'" style="width:'.$widthType.'px;padding:2px 5px">';
      echo '  <table style="width:100%"><tr>';
      echo '    <td style="text-align:left">'.SqlList::getNameFromId('ProjectType', $project->idProjectType).'</td>';
      echo '    <td style="width:20px">';
      if ( ($typ=='PRP' and $alteredType!='OPE') or $alteredType=='PRP') $lockedClass="Locked";
      else  $lockedClass="UnLocked";  
      echo '      <div id="scenarioProjectIcon_'.$project->id.'" onClick="scenarioProjectSwitchType('.$project->id.')" class="roundedIconButton icon'.$lockedClass.' iconSize22">&nbsp;</div>';
      echo '    </td>';
      echo '  </tr></table>';
      echo '</td>';
      $alteredDelay=0;
      if (isset($projDelay[$project->id])) {
        $alteredDelay=$projDelay[$project->id];
      }
      echo '<td id="scenarioProjectDelayTD_'.$project->id.'" class="reportTableData'.(($alteredDelay)?' alteredScenario':'').'" style="width:80px;padding:2px 5px">';
       echo '  <table style="width:100%"><tr>';
       echo '    <td style="width:35%">'.htmlFormatDate($project->ProjectPlanningElement->plannedStartDate).'</td>';
       echo '    <td style="width:35%">'.htmlFormatDate($project->ProjectPlanningElement->plannedEndDate).'</td>';
       echo '    <td>';            
       echo '      <div style="width:70px; text-align: center; color: #000000; display:inline-block;" ';
       echo '        dojoType="dijit.form.NumberSpinner" constraints="{min:-100,max:100,places:0,pattern:\'###0\'}"';
       echo '        intermediateChanges="true" maxlength="3"  class="input  generalColClass "  value="'.$alteredDelay.'"';
       echo '        smallDelta="1" id="scenarioProjectDelay_'.$project->id.'">';
       echo '        <script type="dojo/connect" event="onChange" >  scenarioProjectSwitchDelay('.$project->id.')</script>';
       echo '      </div>';
       echo '    </td>';
       echo '  </tr></table>';   
      echo '</td>';
      echo '</tr>';
    }  
    echo "</table>";
  }
}
?>