<?php
/**
 * @copyright Copyright (C) 2010 Tailored Solutions. All rights reserved.
 * @license GNU/GPL, see license.txt or http://www.gnu.org/copyleft/gpl.html
 * Developed by Tailored Solutions - ijoomer.com
 *
 * ijoomer can be downloaded from www.ijoomer.com
 * ijoomer is free software; you can redistribute it and/or
 * modify it under the terms of the GNU General Public License 2
 * as published by the Free Software Foundation.
 *
 * You should have received a copy of the GNU General Public License
 * along with ijoomer; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301, USA.
 */ 

defined( '_JEXEC' ) or die ( 'Restricted access' );
jimport( 'joomla.application.component.helper' );
jimport( 'joomla.filesystem.folder' );
jimport( 'joomla.version' );
jimport( 'joomla.utilities.date' );
jimport( 'joomla.environment.request' );
jimport( 'joomla.application.component.view' );

define( 'SOBIPRO'		,	true);
define( 'SOBI_ROOT'		,	JPATH_ROOT );
define( 'SOBI_PATH'		,	SOBI_ROOT.DS.'components'.DS.'com_sobipro' );
define( 'SOBI_DEFLANG'	,	JFactory::getConfig()->getValue( 'config.language' ) );
define( 'SOBI_TASK'		,	'task' );
define( 'SOBI_CMS'		,	'joomla16' );

require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "base" . DS . "fs" . DS . "loader.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "env" . DS . "cookie.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "base" . DS . "object.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "base" . DS . "config.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "base" . DS . "factory.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "base" . DS . "registry.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "base" . DS . "request.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "base" . DS . "const.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "cms"  . DS ."joomla16" . DS ."base" . DS . "lang.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "cms"  . DS ."joomla_common" . DS ."base" . DS . "lang.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "sobi.php");
if (file_exists(JPATH_SITE .'/components/com_sobipro/lib/models/review.php')) {
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "models" . DS . "review.php");
}
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "models" . DS . "fields". DS . "interface.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "opt" . DS . "fields" . DS . "fieldtype.php");
if (file_exists(JPATH_SITE .'/components/com_sobipro/opt/fields/geomap.php')) {
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "opt" . DS . "fields" . DS . "geomap.php");
}
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "cms" . DS . "joomla_common" . DS . "base"  .DS . "mainframe.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "ctrl" . DS . "interface.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "ctrl" . DS . "controller.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "base" . DS . "exception.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "base" . DS . "fs" . DS . "loader.php");
SPLoader::loadView( 'view' );
SPLoader::loadClass( 'cms.base.fs' );
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "base" .DS . "filter.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "models" . DS ."section.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "models" .DS . "datamodel.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "models" .DS . "dbobject.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "ctrl" . DS . "entry.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "models" .DS . "entry.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "models" .DS . "category.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "views" .DS . "view.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "views" .DS . "entry.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "ctrl" . DS . "entry.php");
require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "ctrl" . DS . "section.php");
if (file_exists(JPATH_SITE .'/components/com_sobipro/lib/models/review.php')) {
	class tmprating extends SPReview{
		function __construct($rid=0){
			parent::__construct($rid);
		}
	}
}

class tmpmodel extends SPController{
	
}

class tmpobject extends SPDBObject{
	
}

class isobipro extends SPEntryCtrl {
	
	private $IJUserID;
	private $mainframe; 
	private $db;
	private $my;
	private $jsonarray=array();
	
	function __construct(){
		$this->mainframe	=	& JFactory::getApplication();
		$this->db			=	& JFactory::getDBO(); // set database object
		$this->IJUserID		=	$this->mainframe->getUserState('com_ijoomeradv.IJUserID', 0); //get login user id
		$this->my			=	JFactory::getUser($this->IJUserID); // set the login user object
		
		//fetch ijoomer sobipro config
		$query="SELECT config_name,config_value 
				FROM #__ijoomeradv_sobipro_config";
		$this->db->setQuery($query);
		$this->ijoomer_sobipro_config = $this->db->loadAssocList();
    }
    
	protected function tKey( $section, $key, $default = null ){	
		return isset( $this->_tCfg[ $section ][ $key ] ) ? $this->_tCfg[ $section ][ $key ] : ( isset( $this->_tCfg[ 'general' ][ $key ] ) ? $this->_tCfg[ 'general' ][ $key ] : $default );
	}

	protected function parseOrdering( $subject, $col, $def  ){
		return Sobi::GetUserState( $subject.'.ordering.'.SPLang::nid( Sobi::Section( true ) ), $col, $def );
	}
	
	
	/**
     * @uses This function is used to gettypes based on whatever fields mapped in backend ijoomeradv sobipro config.
	 */
	function gettypes($fieldid){
		if(WEBSITE_FIELD){
			$typeFields=explode(',',WEBSITE_FIELD);
			
			$typeArr = array();
			foreach($typeFields as $field){
				$fld=explode(':',$field);
				$fidd[]=$fld[1];
			}
			$farr=array_intersect($fidd,$fieldid);
			foreach($farr as $kw=>$vw){
				$typeArr[$vw]='website';
				$typeArrs['web']=$typeArr;
			}
		}
		
		if(PHONE_FIELD){
			$typephoneFields=explode(',',PHONE_FIELD);
			$typeArr = array();
			foreach($typephoneFields as $phfield){
				$fldp=explode(':',$phfield);
				$fiddp[]=$fldp[1];
			}
			$farrp=array_intersect($fiddp,$fieldid);
			foreach($farrp as $kp=>$vp){
				$typeArr[$vp]='phone';
				$typeArrs['phone']=$typeArr;
			}
		}
		
		if(EMAIL_FIELD){
			$typemailFields=explode(',',EMAIL_FIELD);
			$typeArr = array();
			foreach($typemailFields as $mailfield){
				$fldm=explode(':',$mailfield);
				$fiddm[]=$fldm[1];
			}
			$farrm=array_intersect($fiddm,$fieldid);
			foreach($farrm as $ke=>$ve){
				$typeArr[$ve]='email';
				$typeArrs['email']=$typeArr;
			}
		}
		
		if(CITY_FIELD){
			$typecityFields=explode(',',CITY_FIELD);
			$typeArr = array();
			foreach($typecityFields as $cityfield){
				$fldc=explode(':',$cityfield);
				$fiddc[]=$fldc[1];
			}
			$farrc=array_intersect($fiddc,$fieldid);
			foreach($farrc as $kc=>$vc){
				$typeArr[$vc]='city';
				$typeArrs['city']=$typeArr;
			}
		}
		
		if(STATE_FIELD){
			$typestateFields=explode(',',STATE_FIELD);
			$typeArr = array();
			foreach($typestateFields as $statefield){
				$flds=explode(':',$statefield);
				$fidds[]=$flds[1];
			}
			$farrs=array_intersect($fidds,$fieldid);				
			foreach($farrs as $ks=>$vs){
				$typeArr[$vs]='state';
				$typeArrs['state']=$typeArr;
			}
		}
		
		if(COUNTRY_FIELD){
			$typecountryFields=explode(',',COUNTRY_FIELD);
			$typeArr = array();
			foreach($typecountryFields as $countryfield){
				$fldco=explode(':',$countryfield);
				$fiddco[]=$fldco[1];
			}
			$farrco=array_intersect($fiddco,$fieldid);
			foreach($farrco as $kco=>$vco){
				$typeArr[$vco]='country';
				$typeArrs['country']=$typeArr;
			}
		}
		
		if(ZIP_FIELD){
			$typezipFields=explode(',',ZIP_FIELD);
			$typeArr = array();
			foreach($typezipFields as $zipfield){
				$fldz=explode(':',$zipfield);
				$fiddz[]=$fldz[1];
			}
			$farrz=array_intersect($fiddz,$fieldid);
			foreach($farrz as $kz=>$vz){
				$typeArr[$vz]='zipcode';
				$typeArrs['zipcode']=$typeArr;
			}
		}
		
		if(ADDRESS1_FIELD){
			$typeadd1Fields=explode(',',ADDRESS1_FIELD);
			$typeArr = array();
			foreach($typeadd1Fields as $add1field){
				$fldad1=explode(':',$add1field);
				$fiddad1[]=$fldad1[1];
			}
			$farrad1=array_intersect($fiddad1,$fieldid);
			foreach($farrad1 as $kad=>$vad){
				$typeArr[$vad]='address1';
				$typeArrs['address1']=$typeArr;
			}
		}
		
		if(ADDRESS2_FIELD){
			$typeadd2Fields=explode(',',ADDRESS2_FIELD);
			$typeArr = array();
			foreach($typeadd2Fields as $add2field){
				$fldad2=explode(':',$add2field);
				$fiddad2[]=$fldad2[1];
			}
			$farrad2=array_intersect($fiddad2,$fieldid);
			foreach($farrad2 as $kad2=>$vad2){
				$typeArr[$vad2]='address2';
				$typeArrs['address2']=$typeArr;
			}
		}
		
		if(DEAL_START){
			$typeDSFields=explode(',',DEAL_START);
			$typeArr = array();
			foreach($typeDSFields as $startfield){
				$fldst=explode(':',$startfield);
				$fiddst[]=$fldst[1];
			}
			$farrst=array_intersect($fiddst,$fieldid);
			foreach($farrst as $kst=>$vst){
				$typeArr[$vst]='datetime';
				$typeArrs['startdate']=$typeArr;
			}
		}
		
		if(DEAL_END){
			$typeDEFields=explode(',',DEAL_END);
			$typeArr = array();
			foreach($typeDEFields as $endfield){
				$fldend=explode(':',$endfield);
				$fiddend[]=$fldz[1];
			}
			$farrend=array_intersect($fiddend,$fieldid);
			foreach($farrend as $ken=>$ven){
				$typeArr[$ven]='datetime';
				$typeArrs['enddate']=$typeArr;
			}
		}
		
		if(DEAL_TEXT){
			$typeDTFields=explode(',',DEAL_TEXT);
			$typeArr = array();
			foreach($typeDTFields as $dealtext){
				$flddealtxt=explode(':',$dealtext);
				$fidddealtxt[]=$flddealtxt[1];
			}
			$farrdealtxt=array_intersect($fidddealtxt,$fieldid);
			foreach($farrdealtxt as $kdt=>$vdt){
				$typeArr[$vdt]='dealtext';
				$typeArrs['dealtext']=$typeArr;
			}
		}
		
		if(DEAL_DESCRIPTION){
			$typeDESFields=explode(',',DEAL_DESCRIPTION);
			$typeArr = array();
			foreach($typeDESFields as $desfield){
				$fldes=explode(':',$desfield);
				$fiddes[]=$fldes[1];
			}
			$farrdes=array_intersect($fiddes,$fieldid);
			foreach($farrdes as $kdes=>$vdes){
				$typeArr[$vdes]='dealdescription';
				$typeArrs['dealdescription']=$typeArr;
			}
		}
		return $typeArrs;
	}
	
	
	/**
     * @uses This function is used to getDealtypes based on whatever fields mapped in backend ijoomeradv sobipro config.
	 */
	function getDealTypes($fieldid){
		if(ADDRESS1_FIELD){
			$typeadd1Fields=explode(',',ADDRESS1_FIELD);
			$typeArr = array();
			foreach($typeadd1Fields as $add1field){
				$fldad1=explode(':',$add1field);
				$fiddad1[]=$fldad1[1];
			}
			$farrad1=array_intersect($fiddad1,$fieldid);
			foreach($farrad1 as $kad=>$vad){
				$typeArr[$vad]='address1';
				$typeArrs['address1']=$typeArr;
			}
		}
		if(ADDRESS2_FIELD){
			$typeadd2Fields=explode(',',ADDRESS2_FIELD);
			$typeArr = array();
			foreach($typeadd2Fields as $add2field){
				$fldad2=explode(':',$add2field);
				$fiddad2[]=$fldad2[1];
			}
			$farrad2=array_intersect($fiddad2,$fieldid);
			foreach($farrad2 as $kad2=>$vad2){
				$typeArr[$vad2]='address2';
				$typeArrs['address2']=$typeArr;
			}
		}
		if(DEAL_START){
			$typeDSFields=explode(',',DEAL_START);
			$typeArr = array();
			foreach($typeDSFields as $startfield){
				$fldst=explode(':',$startfield);
				$fiddst[]=$fldst[1];
			}
			$farrst=array_intersect($fiddst,$fieldid);
			foreach($farrst as $kst=>$vst){
				$typeArr[$vst]='startdate';
				$typeArrs['startdate']=$typeArr;
			}
		}
		if(DEAL_END){
			$typeDEFields=explode(',',DEAL_END);
			$typeArr = array();
			foreach($typeDEFields as $endfield){
				$fldend=explode(':',$endfield);
				$fiddend[]=$fldz[1];
			}
			$farrend=array_intersect($fiddend,$fieldid);
			foreach($farrend as $ken=>$ven){
				$typeArr[$ven]='enddate';
				$typeArrs['enddate']=$typeArr;
			}
		}
		if(DEAL_TEXT){
			$typeDTFields=explode(',',DEAL_TEXT);
			$typeArr = array();
			foreach($typeDTFields as $dealtext){
				$flddealtxt=explode(':',$dealtext);
				$fidddealtxt[]=$flddealtxt[1];
			}
			$farrdealtxt=array_intersect($fidddealtxt,$fieldid);
			foreach($farrdealtxt as $kdt=>$vdt){
				$typeArr[$vdt]='dealtext';
				$typeArrs['dealtext']=$typeArr;
			}
		}
		if(DEAL_DESCRIPTION){
			$typeDESFields=explode(',',DEAL_DESCRIPTION);
			$typeArr = array();
			foreach($typeDESFields as $desfield){
				$fldes=explode(':',$desfield);
				$fiddes[]=$fldes[1];
			}
			$farrdes=array_intersect($fiddes,$fieldid);
			foreach($farrdes as $kdes=>$vdes){
				$typeArr[$vdes]='dealdescription';
				$typeArrs['dealdescription']=$typeArr;
			}
		}
		if(MENU_FIELD){
			$typemenuSFields=explode(',',MENU_FIELD);
			$typeArr = array();
			foreach($typemenuSFields as $menufield){
				$fldmenu=explode(':',$menufield);
				$fiddmenu[]=$fldmenu[1];
			}
			$farrmenu=array_intersect($fiddmenu,$fieldid);
			foreach($farrmenu as $kme=>$vme){
				$typeArr[$vme]='menu';
				$typeArrs['menu']=$typeArr;
			}
		}
		return $typeArrs;
	}
	/**
     * @uses This function is used to get Nearby entries(distance wise) between dealstart date to dealend date.
     * @example the json string will be like, : 
     *	{
	 *		"extName":"sobipro",
	 *		"extView":"isobipro",
 	 *		"extTask":"getDeals",
	 * 		"taskData":{
	 * 			"sectionID":"sectionID",
	 * 			"latitude":"latitude",
	 * 			"longitude":"longitude"
	 * 			}
	 * 	}
	 */
	function getDeals(){
		$section 	 = IJReq::getTaskData('sectionID');
		$latitude  	 = IJReq::getTaskData('latitude',0);
		$longitude   = IJReq::getTaskData('longitude',0);
		
		$query ="SELECT sid,
				3956 * 2 * ASIN(SQRT( POWER(SIN(($latitude -
				abs(latitude)) * pi()/180 / 2),2) + COS($latitude * pi()/180 ) * COS(abs(latitude) * pi()/180) * POWER(SIN(($longitude - longitude) * pi()/180 / 2), 2) )) as distance
				FROM #__sobipro_field_geo 
				WHERE section={$section}
				ORDER BY distance ASC";
		$this->db->setQuery($query);
		$entryIDss = $this->db->loadAssocList();
		
		foreach($entryIDss as $entryIDval){
			$enids[]=$entryIDval['sid'];
			$distanceArray[$entryIDval['sid']]=round($entryIDval['distance']);
		}
			
		foreach($enids as $enid){
			$entry = SPFactory::Entry( $enid );
			$fields = $entry->getFields();
			$f = array();
			if( count( $fields ) ) {
				foreach ( $fields as $field ) {
					if( $field->enabled( 'details' ) && $field->get( 'id' ) != Sobi::Cfg( 'entry.name_field' ) ) {
						$struct = $field->struct();
						$options = null;
						if( isset( $struct[ '_options' ] ) ) {
							$options = $struct[ '_options' ];
							unset( $struct[ '_options' ] );
						}
						$f[ $field->get( 'nid' ) ] = array(
							'_complex' => 1,
							'_data' => array(
									'label' => array(
										'_complex' => 1,
										'_data' => $field->get( 'name' ),
										'_attributes' => array( 'lang' => Sobi::Lang( false ), 'show' => $field->get( 'withLabel' ) )
									),
									'data' => $struct,
							),
							'_attributes' => array( 'id' => $field->get( 'id' ), 'type' => $field->get( 'type' ), 'suffix' => $field->get( 'suffix' ), 'position' => $field->get( 'position' ), 'css_class' => ( strlen( $field->get( 'cssClass' ) ) ? $field->get( 'cssClass' ) : 'spField' ) )
						);
						if( Sobi::Cfg( 'entry.field_description', false ) ) {
							$f[ $field->get( 'nid' ) ][ '_data' ][ 'description' ] = array( '_complex' => 1, '_xml' => 1, '_data' => $field->get( 'description' ) );
						}
						if( $options ) {
							$f[ $field->get( 'nid' ) ][ '_data' ][ 'options' ] = $options;
						}
						if( isset( $struct[ '_xml_out' ] ) && count( $struct[ '_xml_out' ] ) ) {
							foreach( $struct[ '_xml_out' ] as $k => $v )
								$f[ $field->get( 'nid' ) ][ '_data' ][ $k ] = $v;
						}
					}
				}
				$endateField[ 'field_deal_start_id' ] = $f['field_deal_start']['_attributes']['id'];
				$endateField[ 'field_deal_end_id' ] = $f['field_deal_end']['_attributes']['id'];
				$dateFields[]=$endateField;
			}
		}
			
		$startDateID = $dateFields[0]['field_deal_start_id'];
		$endDateID = $dateFields[0]['field_deal_end_id'];
		$curtime =  gmmktime(date("H"),date("i"),date("s"),date("m"),date("d"),date("Y") );
		$query="SELECT sid 
				FROM `#__sobipro_field_data` WHERE fid=$startDateID AND baseData < $curtime 
				AND sid in (SELECT sid 
				FROM `#__sobipro_field_data` WHERE fid=$endDateID AND baseData > $curtime)";
		if($latitude!='' && $latitude!=0){
			$query.= " AND sid in(".implode(',',$enids).")";
		}
		$this->db->setQuery($query);
		$entriesids = $this->db->loadResultArray();
		$uentries=array_unique($entriesids);
		
		foreach($uentries as $uid){
			$entry = SPFactory::Entry( $uid );
        	$en[ 'id' ] = $entry->get( 'id' );
            $en[ 'nid' ] = $entry->get( 'nid' );
            $en[ 'name' ] = array(
                '_complex' => 1,
                '_data' => $entry->get( 'name' ),
                '_attributes' => array( 'lang' => Sobi::Lang( false ) )
            );
            //
            $fields = $entry->getFields();
			$f = array();
			
			if( count( $fields ) ) {
				foreach ( $fields as $field ) {
					if( $field->enabled( 'details' ) && $field->get( 'id' ) != Sobi::Cfg( 'entry.name_field' ) ) {
						$struct = $field->struct();
						$options = null;
						if( isset( $struct[ '_options' ] ) ) {
							$options = $struct[ '_options' ];
							unset( $struct[ '_options' ] );
						}
						$f[ $field->get( 'nid' ) ] = array(
							'_complex' => 1,
							'_data' => array(
									'label' => array(
										'_complex' => 1,
										'_data' => $field->get( 'name' ),
										'_attributes' => array( 'lang' => Sobi::Lang( false ), 'show' => $field->get( 'withLabel' ) )
									),
									'data' => $struct,
							),
							'_attributes' => array( 'id' => $field->get( 'id' ), 'type' => $field->get( 'type' ), 'suffix' => $field->get( 'suffix' ), 'position' => $field->get( 'position' ), 'css_class' => ( strlen( $field->get( 'cssClass' ) ) ? $field->get( 'cssClass' ) : 'spField' ) )
						);
						if( Sobi::Cfg( 'entry.field_description', false ) ) {
							$f[ $field->get( 'nid' ) ][ '_data' ][ 'description' ] = array( '_complex' => 1, '_xml' => 1, '_data' => $field->get( 'description' ) );
						}
						if( $options ) {
							$f[ $field->get( 'nid' ) ][ '_data' ][ 'options' ] = $options;
						}
						if( isset( $struct[ '_xml_out' ] ) && count( $struct[ '_xml_out' ] ) ) {
							foreach( $struct[ '_xml_out' ] as $k => $v )
								$f[ $field->get( 'nid' ) ][ '_data' ][ $k ] = $v;
						}
					}
				}
				$en[ 'fields' ] = $f;
				$entries[]=$en;
			}
           
        }
        
        if(count($entries)==0){
        	IJReq::setResponseCode(204);
        }
          
        foreach($entries as $enK=>$enV){
        	$entryArray['entries'][$enK]['id'] = $enV['id'];
        	$entryArray['entries'][$enK]['distance'] = $distanceArray[$enV['id']];
			$entryArray['entries'][$enK]['name'] = $enV['name']['_data'];
        	$query="SELECT fid,latitude,longitude   
					FROM #__sobipro_field_geo 
					WHERE section={$section} 
					AND sid={$enV['id']}";
			$this->db->setQuery($query);
			$mapfields = $this->db->loadObject();
			$entryArray['entries'][$enK]['latitude']=($mapfields->latitude) ? $mapfields->latitude : '';
			$entryArray['entries'][$enK]['longitude']=($mapfields->longitude) ? $mapfields->longitude : '';
			$fields = $enV['fields'];
			
			foreach($fields as $fk=>$fv){
	    		$fieldID = $fv['_attributes']['id'];
	    		$fieldIDs[]=$fieldID;
	    	}
	    	
	    	$dealTypes=$this->getDealTypes($fieldIDs);
	   
        	foreach($fields as $fkey=>$fval){
				$caption = $fval['_data']['label']['_data'];
				$type    = $fval['_attributes']['type'];
				$value   = $fval['_data']['data']['_data'];
				$fieldID = $fval['_attributes']['id'];	
						
				if($type=='image'){
					if(isset($value['img']['_attributes']['src'])){
						$entryArray['entries'][$enK]['img_galleries'][]=JURI::base() .$value['img']['_attributes']['src'];
					}
				}
				
				foreach($dealTypes as $TypeValues){
		    		foreach($TypeValues as $tyke=>$tyval){
	        			 if($type=='calendar'){
							$entryArray['entries'][$enK][$caption]=$value;
						 }
	        			 if((($type=='inbox') && $fieldID==$tyke) || $caption=='Valid For'){	
							$entryArray['entries'][$enK][$caption]=$value;
						 }
	        			 if(($type=='textarea') && $fieldID==$tyke){
							$entryArray['entries'][$enK][$caption]=$value;
						 }
					} 
				}
        	}
			
        }
        $jsonarray = array();
    	$jsonarray['code']		 = 200;
    	$jsonarray['entries']	 =($entryArray['entries']!="") ? $entryArray['entries'] : '';
		return $jsonarray;
		
	}
	
	/**
     * @uses This function is used to get all categories,items,reviews and all details of selected sectionid or categoryid.
     * @example the json string will be like, : 
     *	{
	 *		"extName":"sobipro",
	 *		"extView":"isobipro",
 	 *		"extTask":"sectionCategories",
	 * 		"taskData":{
	 * 			"sectionID":"sectionID",(optional)
	 * 			"catID":"catID",(optional)
	 * 			"sortBy":"sortBy",(rating/title)
	 * 			"sortOrder":"sortOrder",(asc/desc)
	 *   		"filterBy":"filterBy",
	 *          "latitude":"latitude",
	 * 			"longitude":"longitude",
	 * 			"featuredFirst":"featuredFirst",(Yes/No)
	 * 			"pageNO":"pageNO"
	 * 			}
	 * 	}
	 */
	function sectionCategories(){
    	$sectionID  = IJReq::getTaskData('sectionID',0,'int');
    	$catID  = IJReq::getTaskData('categoryID',0,'int');
    	$featuredFirst = IJReq::getTaskData('featuredFirst');
		if(strpos($_REQUEST['reqObject'],'latitude') > 0) {
			$latitude = IJReq::getTaskData('latitude',0);
			$longitude = IJReq::getTaskData('longitude',0);
		} else {
			//variable is not set
		}
    	
    	SPFactory::registry()->set( 'current_section',json_decode(json_encode($sectionID), FALSE));
    	defined( 'SOBI_ACL' ) || define( 'SOBI_ACL', 'front' );
    	
    	if (file_exists(JPATH_SITE .'/components/com_sobipro/lib/models/review.php')) {
    		$tmpreviewobject = new tmprating();
    	}
    	$sortBy=IJReq::getTaskData('sortBy');
    	$sortOrder=IJReq::getTaskData('sortOrder');
    	$filterBy=IJReq::getTaskData('filterBy');
    	$FilterBy = explode(',',$filterBy);
    	$pageNO = IJReq::getTaskData('pageNO');
    	$entryLimit= PAGE_LIMIT;
    	
		$cfginstanse = SPConfig::getInstance();
    	$cfginstanse->addIniFile( 'etc.config', true );
        $cfginstanse->addTable( 'spdb_config', $sectionID );
        /* initialise interface config setting */
        $apmainframe = & SPFactory::mainframe();
        $apmainframe->getBasicCfg();
    	$cOrder = $this->parseOrdering( 'categories', 'corder', $this->tKey( 'view', 'categories_ordering', Sobi::Cfg( 'list.categories_ordering', 'name.asc' ) ) );
    	$cLim = $this->tKey( 'view', 'categories_limit', -1 );
		$eLimit = $this->tKey( 'view', 'entries_limit', Sobi::Cfg( 'list.entries_limit', 2 ) );
    	
    	$itemLimit=($entryLimit) ? $entryLimit : $eLimit;
    	$eLimStart=($pageNO<=1) ? 0 : $itemLimit*($pageNO-1);
	    	
    	$SPSectionCtrl = new SPSectionCtrl();
    	$SPSectionCtrl->setModel('SPCategory');

    	$obj = ($sectionID && !($catID)) ? SPFactory::object( $sectionID ) : SPFactory::object( $catID ); 
    	$SPSectionCtrl->extend($obj);
    	$catid = $SPSectionCtrl->getCats( $cOrder, $cLim );
    	$si = SPSection::getInstance($catID);
    	
    	$query="SELECT so.name
				FROM #__sobipro_object as so 
				WHERE so.oType='section'
    			AND so.id={$sectionID}";
    	$this->db->setQuery($query);
        $sectionName = $this->db->loadResult();
       		
    	$categories = array();
    	foreach ($catid as $cid){
		   	$category = SPFactory::Category($cid);
		    $cat[ 'id' ] = $category->get( 'id' );
			$cat[ 'nid' ] = $category->get( 'nid' );
			$cat[ 'name' ] = array(
			    '_complex' => 1,
			    '_data' => $category->get( 'name' ),
			    '_attributes' => array( 'lang' => Sobi::Lang( false ) )
			);
			
			$cat[ 'description' ] = array(
			        '_complex' => 1,
			        '_cdata' => 1,
			        '_data' => $category->get( 'description' ),
			        '_attributes' => array( 'lang' => Sobi::Lang( false ) )
			    );
			
			$showIcon = $category->get( 'showIcon' );
			if ( $showIcon == SPC::GLOBAL_SETTING ) {
			    $showIcon = Sobi::Cfg( 'category.show_icon', true );
			}
			if ( $showIcon && $category->get( 'icon' ) ) {
			  $cat[ 'icon' ] = Sobi::FixPath( Sobi::Cfg( 'images.category_icons_live' ) . $category->get( 'icon' ) );
			}
			$cat[ 'url' ] = Sobi::Url( array( 'title' => $category->get( 'name' ), 'sid' => $category->get( 'id' ) ) );
			$cat[ 'position' ] = $category->get( 'position' );
			$cat[ 'author' ] = $category->get( 'owner' );
			if ( $category->get( 'state' ) == 0 ) {
				$cat[ 'state' ] = 'unpublished';
			}else if ( strtotime( $category->get( 'validUntil' ) ) != 0 && strtotime( $category->get( 'validUntil' ) ) < time() ) {
				$cat[ 'state' ] = 'expired';
			}else if( strtotime( $category->get( 'validSince' ) ) != 0 && strtotime( $category->get( 'validSince' ) ) > time() ) {
				$cat[ 'state' ] = 'pending';
			}else {
				$cat[ 'state' ] = 'published';
			}
			$categories[]=$cat;
			unset( $category );
    	}
    	
    	foreach($categories as $catkey=>$catvalue){
    		$catName = $catvalue['name']['_data'];
    		$catDesc = $catvalue['description']['_data'];
			$categoryArray['categories'][$catkey]['id'] 	         = $catvalue['id'];
			$categoryArray['categories'][$catkey]['name'] 		 	 = $catName;
			$categoryArray['categories'][$catkey]['description']    = strip_tags($catDesc);
			$categoryArray['categories'][$catkey]['image'] = ($catvalue['icon']) ? $catvalue['icon'] : "";	
		}
    	JRequest::setVar( 'sid', $catID );
       	$entriesRecursive = $this->tKey( 'view', 'entries_recursive', Sobi::Cfg( 'list.entries_recursive', false ) );
		$eOrder = $this->parseOrdering( 'entries', 'eorder', $this->tKey( 'view', 'entries_ordering', Sobi::Cfg( 'list.entries_ordering', 'name.asc' ) ) );
		if($catID){
			$entryIDs = $SPSectionCtrl->getEntries( $eOrder, '', '', false, null, $entriesRecursive );
			$totalEntries = count($SPSectionCtrl->getEntries( $eOrder, '', '', false, null, $entriesRecursive ));
		}
       
        if($latitude!='' && $longitude!=''){
	        if($sectionID!="" && $latitude!="" && $longitude!="" && !($catID)){
	    		$implode=implode(',',$catid);
	    		$query="SELECT so.id 
						FROM #__sobipro_object as so 
						WHERE so.parent IN (".$implode.")
						AND so.state=1"; 
				$this->db->setQuery($query);
				$totalentryIDs = $this->db->loadResultArray();
				$query="SELECT sid,
						3956 * 2 * ASIN(SQRT( POWER(SIN(($latitude -
						abs(latitude)) * pi()/180 / 2),2) + COS($latitude * pi()/180 ) * COS(abs(latitude) * pi()/180) * POWER(SIN(($longitude - longitude) * pi()/180 / 2), 2) )) as distance
						FROM #__sobipro_field_geo 
						WHERE section={$sectionID}
						AND sid in (".implode(',',$totalentryIDs).")
						ORDER BY distance ASC";
				$this->db->setQuery($query);
				$entryIDss = $this->db->loadAssocList();
				foreach($entryIDss as $entryIDval){
					$entryIDs[]=$entryIDval['sid'];
					$distanceArray[$entryIDval['sid']]=round($entryIDval['distance']);
					
				}
			}
	    	if($sectionID!="" && !($catID) && $filterBy!=""){
    			$query="SELECT so.id 
						FROM #__sobipro_object as so 
						WHERE so.parent IN (".$filterBy.")"; 
				$this->db->setQuery($query);
				$entryIDs = $this->db->loadResultArray();
	    	}
		}else if($sectionID!='' && !($catID) && $latitude==0 && $longitude==0){
    		$implode=implode(',',$catid);
    	    $query="SELECT so.id 
					FROM #__sobipro_object as so 
					WHERE so.parent IN (".$implode.")
					AND so.state=1"; 
			$this->db->setQuery($query);
			$entryIDs = $this->db->loadResultArray();
       		if($filterBy!=""){
    			$query="SELECT so.id 
						FROM #__sobipro_object as so 
						WHERE so.parent IN (".$filterBy.")"; 
				$this->db->setQuery($query);
				$entryIDs = $this->db->loadResultArray();
    		}
		}
	    
        foreach ($entryIDs as $enid){
        	$entry = SPFactory::Entry( $enid );
        	$en[ 'id' ] = $entry->get( 'id' );
            $en[ 'nid' ] = $entry->get( 'nid' );
            $en[ 'name' ] = array(
                '_complex' => 1,
                '_data' => $entry->get( 'name' ),
                '_attributes' => array( 'lang' => Sobi::Lang( false ) )
            );
            $en[ 'url_array' ] = array( 'title' => $entry->get( 'name' ), 'pid' => $entry->get( 'primary' ), 'sid' => $entry->get( 'id' ) );
            if ( strstr( SPRequest::task(), 'search' ) || $noId || ( Sobi::Cfg( 'section.force_category_id', false ) && SPRequest::sid() == Sobi::Section() ) ) {
                $en[ 'url' ] = Sobi::Url( array( 'title' => $entry->get( 'name' ),
                    'pid' => $entry->get( 'primary' ), 'sid' => $entry->get( 'id' ) ) );
            }else {
                $en[ 'url' ] = Sobi::Url( array( 'title' => $entry->get( 'name' ),
                    'pid' => SPRequest::sid(), 'sid' => $entry->get( 'id' ) ) );
            }
            if ( Sobi::Cfg( 'list.entry_meta', true ) ) {
                $en[ 'meta' ] = array(
                    'description' => $entry->get( 'metaDesc' ),
                    'keys' => $this->metaKeys( $entry ),
                    'author' => $entry->get( 'metaAuthor' ),
                    'robots' => $entry->get( 'metaRobots' ),
                );
            }
            if ( $manager || ( ( Sobi::My( 'id' ) && ( Sobi::My( 'id' ) == $entry->get( 'owner' ) ) && Sobi::Can( 'entry', 'edit', 'own', Sobi::Section() ) ) ) ) {
                $en[ 'edit_url' ] = Sobi::Url( array( 'task' => 'entry.edit', 'pid' => SPRequest::sid(), 'sid' => $entry->get( 'id' ) ) );
            }else {
                if ( isset( $en[ 'edit_url' ] ) ) {
                    unset( $en[ 'edit_url' ] );
                }
            }
            $en[ 'edit_url_array' ] = array( 'task' => 'entry.edit', 'pid' => SPRequest::sid(), 'sid' => $entry->get( 'id' ) );
            $en[ 'created_time' ] = $entry->get( 'createdTime' );
            $en[ 'updated_time' ] = $entry->get( 'updatedTime' );
            $en[ 'valid_since' ] = $entry->get( 'validSince' );
            $en[ 'valid_until' ] = $entry->get( 'validUntil' );
            if ( $entry->get( 'state' ) == 0 ) {
                $en[ 'state' ] = 'unpublished';
            }else if ( strtotime( $entry->get( 'validUntil' ) ) != 0 && strtotime( $entry->get( 'validUntil' ) ) < time() ) {
				$en[ 'state' ] = 'expired';
            }else if ( strtotime( $entry->get( 'validSince' ) ) != 0 && strtotime( $entry->get( 'validSince' ) ) > time() ) {
                $en[ 'state' ] = 'pending';
            }else {
                $en[ 'state' ] = 'published';
			}
			$en[ 'author' ] = $entry->get( 'owner' );
			$en[ 'counter' ] = $entry->get( 'counter' );
			$en[ 'approved' ] = $entry->get( 'approved' );
	           
			if ( Sobi::Cfg( 'list.entry_cats', true ) ) {
				$cats = $entry->get( 'categories' );
				$categories = array();
				if ( count( $cats ) ) {
					$cn = SPLang::translateObject( array_keys( $cats ), 'name' );
				}
				foreach ( $cats as $cid => $cat ) {
					$categories[ ] = array(
						'_complex' => 1,
						'_data' => SPLang::clean( $cn[ $cid ][ 'value' ] ),
						'_attributes' => array( 'lang' => Sobi::Lang( false ), 'id' => $cat[ 'pid' ], 'position' => $cat[ 'position' ], 'url' => Sobi::Url( array( 'sid' => $cat[ 'pid' ], 'title' => $cat[ 'name' ] ) ) )
					);
				}
				$en[ 'categories' ] = $categories;
			}
			$fields = $entry->getFields();
	                    
			$f = array();
			if( count( $fields ) ) {
				foreach ( $fields as $field ) {
					if( $field->enabled( 'details' ) && $field->get( 'id' ) != Sobi::Cfg( 'entry.name_field' ) ) {
						$struct = $field->struct();
						$options = null;
						if( isset( $struct[ '_options' ] ) ) {
							$options = $struct[ '_options' ];
							unset( $struct[ '_options' ] );
						}
						$f[ $field->get( 'nid' ) ] = array(
							'_complex' => 1,
							'_data' => array(
									'label' => array(
										'_complex' => 1,
										'_data' => $field->get( 'name' ),
										'_attributes' => array( 'lang' => Sobi::Lang( false ), 'show' => $field->get( 'withLabel' ) )
									),
									'data' => $struct,
							),
							'_attributes' => array( 'id' => $field->get( 'id' ), 'type' => $field->get( 'type' ), 'suffix' => $field->get( 'suffix' ), 'position' => $field->get( 'position' ), 'css_class' => ( strlen( $field->get( 'cssClass' ) ) ? $field->get( 'cssClass' ) : 'spField' ) )
						);
						if( Sobi::Cfg( 'entry.field_description', false ) ) {
							$f[ $field->get( 'nid' ) ][ '_data' ][ 'description' ] = array( '_complex' => 1, '_xml' => 1, '_data' => $field->get( 'description' ) );
						}
						if( $options ) {
							$f[ $field->get( 'nid' ) ][ '_data' ][ 'options' ] = $options;
						}
						if( isset( $struct[ '_xml_out' ] ) && count( $struct[ '_xml_out' ] ) ) {
							foreach( $struct[ '_xml_out' ] as $k => $v )
								$f[ $field->get( 'nid' ) ][ '_data' ][ $k ] = $v;
						}
					}
			}
			$en[ 'fields' ] = $f;
			if (file_exists(JPATH_SITE .'/components/com_sobipro/lib/models/review.php')) {
				$tmpreviewobject->setSid($en[ 'id' ]);
	    		$entry=array();
	    		$entry[ 'entry' ][ '_data' ][ 'name' ][ '_data' ] = $en['name']['_data'];
	    		$query="SELECT count(rid)   
						FROM #__sobipro_sprr_review  
						WHERE sid={$en[ 'id' ]}"; 
				$this->db->setQuery($query);
				$totalreview = $this->db->loadResult();
				$tmpreviewobject->revOnSite = $totalreview;
		    	$details = $tmpreviewobject->setDetails($entry, $site = 1);
		    	$en['reviews'] = round($entry['reviews']['summary_rating']['overall']['_attributes']['value']);
			}
			$entries[]=$en;
        }
      }
        $namearray = array();
        foreach($entries as $namek=>$nval){
        	$total_categories=$nval['categories'];
        	$categoryNames = array();
        	foreach($total_categories as $feke=>$feval){
        		$categoryNames[]=$feval['_data'];
        	}
        	if(in_array('Featured',$categoryNames)){
				$featureCatIds[] = $nval;
			} else {
				$catIds[] = $nval;
			}
        }
        
        if($featuredFirst=='Yes') {
        	$entries = ($featureCatIds!='' && $catIds!='') ?
        		 array_merge($featureCatIds,$catIds) : ($featureCatIds=='') ? $catIds : $featureCatIds;
        }
        
    	foreach($entries as $namek=>$nval){
        	$fieldprices[]=$nval['fields']['field_price']['_data']['data']['_data'];
        	$namearray[]=$nval['name']['_data'];
        	$averagerating[]=$nval['reviews'];
        }
        
        if($sortBy=='title'){
	    	switch ($sortOrder){
				case 'asc' :
		        	array_multisort($namearray, SORT_ASC, $entries);
		            break;
		
		        case 'desc' :
		        	array_multisort($namearray, SORT_DESC, $entries);
		            break;
			}
        }
      
    	if($sortBy=='rating'){
	    	switch ($sortOrder){
				case 'asc' :
		        	array_multisort($averagerating, SORT_ASC, $entries);
		            break;
		
		        case 'desc' :
		        	array_multisort($averagerating, SORT_DESC, $entries);
					break;
			}
        }
        
    	if($sortBy=='price'){
	    	switch ($sortOrder){
				case 'asc' :
					array_multisort($fieldprices, SORT_ASC, $entries);
					break;
		
				case 'desc' :
					array_multisort($fieldprices, SORT_DESC, $entries);
					break;
			}
		}
        
        if($filterBy!="" && $catID!=''){
	    	$new = array();
	    	foreach($averagerating as $avKey=>$avVal){
	    		if(in_array($avVal,$FilterBy)){
	    			$new[$avKey]=$avVal;
	    		}else{
	    			unset($averagerating[$avKey]);
	    		}
	    	}
			foreach($new as $newkey=>$newval){
				$c[] = $entries[$newkey];
				
			}
			$entries=$c;
    	}
    	
       	$totalEntries=count($entries);
      	
       	$looplimit = (count($entries)<($itemLimit*$pageNO)) ? count($entries) : ($itemLimit*$pageNO);
		
       	$inc1=0;
    	for($inc=$eLimStart;$inc<$looplimit;$inc++){
			$entryID=$entries[$inc]['id'];			
			$entryArray['entries'][$inc1]['id'] = $entryID;
			$entryArray['entries'][$inc1]['distance'] = ($distanceArray[$entryID]!='') ? $distanceArray[$entryID] : '';
			$entryArray['entries'][$inc1]['sharelink'] = $_SERVER['SERVER_ADDR'].$entries[$inc]['url'];
			$Title=$entries[$inc]['name']['_data'];
			$entryArray['entries'][$inc1]['title'] = $Title;
			
			$query="SELECT fid,latitude,longitude   
					FROM #__sobipro_field_geo 
					WHERE section={$sectionID} 
					AND sid={$entryID}";
			$this->db->setQuery($query);
			$mapfields = $this->db->loadObject();
			$entryArray['entries'][$inc1]['latitude']=($mapfields->latitude) ? $mapfields->latitude : "";
			$entryArray['entries'][$inc1]['longitude']=($mapfields->longitude) ? $mapfields->longitude : "";
			$fields = $entries[$inc]['fields'];
			
			if (file_exists(JPATH_SITE .'/components/com_sobipro/lib/models/review.php')) {
				$tmpreviewobject->setSid($entryID);
	    		$entry=array();
	    		$entry[ 'entry' ][ '_data' ][ 'name' ][ '_data' ] = $Title;
		    	$query="SELECT count(rid)   
						FROM #__sobipro_sprr_review  
						WHERE sid={$entryID}"; 
				$this->db->setQuery($query);
				$totalreview = $this->db->loadResult();
				$tmpreviewobject->revOnSite = $totalreview;
		    	$details = $tmpreviewobject->setDetails($entry, $site = 1);
		    	$reviews = $entry['reviews'];
		    	$over=$entry['reviews']['summary_rating'];
		    	$fieldslabel=$entry['reviews']['summary_rating']['fields'];
			}
	    	if($reviews){	
		    	if($reviews['summary_rating']){
		    		unset($reviews['summary_rating']);unset($reviews['navigation']);
					for($re=0;$re<count($reviews);$re++) {
			    		$positives=implode(",",$reviews[$re]['_data']['input']['positives']);
			    		$negatives=implode(",",$reviews[$re]['_data']['input']['negatives']);
			    		$entryArray['entries'][$inc1]['reviewrating'][$re]['reviewid']=$reviews[$re]['_attributes']['id'];
			    		$entryArray['entries'][$inc1]['reviewrating'][$re]['reviewtitle']=$reviews[$re]['_data']['title'];
			    		$entryArray['entries'][$inc1]['reviewrating'][$re]['review']=$reviews[$re]['_data']['input']['text']['_data'];
			    		$entryArray['entries'][$inc1]['reviewrating'][$re]['reviewpositives']= ($positives) ? $positives : '';
			    		$entryArray['entries'][$inc1]['reviewrating'][$re]['reviewnegatives']=($negatives) ? $negatives : '';
			    		$entryArray['entries'][$inc1]['reviewrating'][$re]['reviewdate']=$reviews[$re]['_attributes']['date'];
						$entryArray['entries'][$inc1]['reviewrating'][$re]['reviewusername']=($reviews[$re]['_data']['author']['_data']!='') ? $reviews[$re]['_data']['author']['_data'] : '';
			    		$entryArray['entries'][$inc1]['reviewrating'][$re]['reviewuserid']=$reviews[$re]['_data']['author']['_attributes']['id'];	
			    		$ratingsArray=$reviews[$re]['_data']['ratings'];
			    		for($ratcount=0;$ratcount<count($ratingsArray);$ratcount++){
				    		$entryArray['entries'][$inc1]['reviewrating'][$re]['ratings'][$ratcount]['ratingid']=$reviews[$re]['_data']['ratings'][$ratcount]['_attributes']['id'];	
				    		$entryArray['entries'][$inc1]['reviewrating'][$re]['ratings'][$ratcount]['ratingvote']=$reviews[$re]['_data']['ratings'][$ratcount]['_data'];
				    		$entryArray['entries'][$inc1]['reviewrating'][$re]['ratings'][$ratcount]['criterionname']=$reviews[$re]['_data']['ratings'][$ratcount]['_attributes']['label'];
			    		}
		    			$entryArray['entries'][$inc1]['reviewrating'][$re]['averagerating']=$reviews[$re]['_attributes']['oar'];
					}
		    	}
	    	}else{
	    		$entryArray['entries'][$inc1]['reviewrating']='';
	    	}
	    	
	    	if($fieldslabel!=''){
				foreach($fieldslabel as $f=>$flabel){
					$entryArray['entries'][$inc1]['criterionaverage'][$f]['criterionname']=$flabel['_attributes']['label'];
					$entryArray['entries'][$inc1]['criterionaverage'][$f]['ratingvote']=$flabel['_attributes']['value'];
				}
	    	}else{
	    		$entryArray['entries'][$inc1]['criterionaverage']='';
	    	}
	    	
	    	$entryArray['entries'][$inc1]['averagerating']=($over['overall']['_attributes']['value']) ? round($over['overall']['_attributes']['value']) : 0 ;
	    	$entryArray['entries'][$inc1]['totalreviewcount']=($over['overall']['_attributes']['count']) ? $over['overall']['_attributes']['count'] : 0 ;
	    	foreach($fields as $fk=>$fv){
	    		$fieldID = $fv['_attributes']['id'];
	    		$fieldIDs[]=$fieldID;
	    	}
	    	$Types=$this->gettypes($fieldIDs);
	    	
	    	$entryArray['entries'][$inc1]['img_galleries']='';
			$i=0;
			foreach($fields as $fkey=>$fval){
				$caption = $fval['_data']['label']['_data'];
				$type    = $fval['_attributes']['type'];
				$value   = $fval['_data']['data']['_data'];
				$fieldID = $fval['_attributes']['id'];
				$query="SELECT sValue   
						FROM #__sobipro_config  
						WHERE cSection='payments' 
						AND sKey='currency'"; 
				$this->db->setQuery($query);
				$currency = $this->db->loadResult();
				$query="SELECT sl.sValue  
						FROM #__sobipro_language as sl 
						WHERE sl.fid={$fieldID}
						AND sl.sKey='suffix'";
				$this->db->setQuery($query);
				$unit=$this->db->loadResult();
				
				if(($caption=='Company Image' || $caption=='Image') 
					&& (isset($value['img']['_attributes']['src']) 
						&& !empty($value['img']['_attributes']['src']))){
					$entryArray['entries'][$inc1]['img_galleries'][]=$value['img']['_attributes']['src'];
				}else {
					if(isset($unit) && $unit!='[cfg:payments.currency]'){
						$entryArray['entries'][$inc1]['field'][$i]['unit']=$unit;
					}else if($unit=='[cfg:payments.currency]'){
						$entryArray['entries'][$inc1]['field'][$i]['unit']=$currency;
					}else{
						$entryArray['entries'][$inc1]['field'][$i]['unit']='';
					}
					$entryArray['entries'][$inc1]['field'][$i]['type']=$type;
					foreach($Types as $TypeValues){
			    		foreach($TypeValues as $tyke=>$tyval){
			    				if($fieldID==$tyke){
			    					$entryArray['entries'][$inc1]['field'][$i]['type']=$tyval;
			    				}
			    		}
		    		}
					$entryArray['entries'][$inc1]['field'][$i]['labelid']=$fkey;
					if($type=='url'){
						$webTitle=$value['a']['_data'];
						$entryArray['entries'][$inc1]['field'][$i]['caption']=(isset($webTitle)) ? $webTitle : '';
					}else{
						$entryArray['entries'][$inc1]['field'][$i]['caption']=$caption;
					}
					if($type=='image' && $caption=='Company Logo'){
						$Images=$value['img']['_attributes']['src'];
						$entryArray['entries'][$inc1]['field'][$i]['value']=($Images!="") ? $Images : '';
					}else if($type=='chbxgroup' || $type=='multiselect'){
						$days=$value['ul']['_data'];
						foreach($days as $dke=>$dval){
							$implodeval[$dke]= $dval['_value'];
						}
						$entryArray['entries'][$inc1]['field'][$i]['value']=implode(',',$implodeval );
					}else if($type=='url'){
						$webValue=$value['a']['_attributes']['href'];
						$entryArray['entries'][$inc1]['field'][$i]['value']=(isset($webValue)) ? $webValue : '';
					}else if($type=='textarea'){
						$entryArray['entries'][$inc1]['field'][$i]['value']=strip_tags($value);
					}else if($type=='geomap'){
						unset($entryArray['entries'][$inc1]['field'][$i]);
					}else if($value){
						$entryArray['entries'][$inc1]['field'][$i]['value']=$value;
					}else{
						$entryArray['entries'][$inc1]['field'][$i]['value']="";
					}
					$i++;
				}
				$entryArray['entries'][$inc1]['field'] = array_values($entryArray['entries'][$inc1]['field']);
			}
			$inc1++;
    	}
	    $jsonarray = array();
	    $jsonarray['code']		 = 200;
	    $jsonarray['sectionid']	 = $sectionID;
	    $jsonarray['sectionname'] =$sectionName;
		$jsonarray['categories'] = $countcategories;
		$jsonarray['catid'] = ($catID) ? $catID : '';
		$jsonarray['total'] = ($totalEntries) ? $totalEntries : '';
		$jsonarray['pageLimit']	 = $itemLimit;
		$jsonarray['categories'] =(($pageNO==0 || $pageNO==1) && isset($categoryArray['categories'])) ? $categoryArray['categories'] : "";
		$jsonarray['entries'] = ($entryArray['entries']) ? $entryArray['entries'] : "";
		return $jsonarray;    
    }
	
    /**
     * @uses This function is used to display search form based on sectionid passed.
     * @example the json string will be like, : 
     *	{
	 *		"extName":"sobipro",
	 *		"extView":"isobipro",
 	 *		"extTask":"getsearchField",
	 * 		"taskData":{
	 * 			"sectionID":"sectionID"
	 * 			}
	 * 	}
	 */
	function getsearchField($tsid=0,$bypass=false){
    	$sectionID=IJReq::getTaskData('sectionID',$tsid);
    	$query="SELECT sf.fid,sf.nid,sf.params,sf.fieldType,sl.sValue  
				FROM #__sobipro_field as sf 
				LEFT JOIN #__sobipro_language as sl on sf.fid=sl.fid 
				WHERE sf.inSearch=1 
				AND sf.enabled=1
				AND sl.oType='field' 
				AND sl.sKey='name' 
				AND sf.section={$sectionID} 
				ORDER BY sf.position";
		$this->db->setQuery($query);
		$fields=$this->db->loadAssoclist();
		
		foreach($fields as $key=>$value){
			$raw = SPConfig::unserialize($value["params"]);
			if(!array_key_exists("searchMethod", $raw) or $raw["searchMethod"]=="general"){
				unset($fields[$key]);
			}else{
				unset($fields[$key]["params"]);
				foreach($raw as $r=>$p){
					$fields[$key][$r]=$p;
				}
			}
			
			/*
			 * fetch the suffix
			 */
			$query="SELECT sl.sKey,sl.sValue  
					FROM #__sobipro_language as sl 
					WHERE sl.fid={$value['fid']}";
			$this->db->setQuery($query);
			$lang=$this->db->loadObject();
			
			if($lang){
				preg_match('|\[|',$lang->sValue,$matches);
				if(isset($matches[0]) and $matches[0]){
					$srch=array('[',']');
					$lang->sValue=str_replace($srch,'',$lang->sValue);
					$tmp=explode(':',$lang->sValue);
					$tmp=explode('.',$tmp[1]);
					
					$query="SELECT sValue  
							FROM #__sobipro_config  
							WHERE cSection='{$tmp[0]}' 
							AND sKey='{$tmp[1]}'";
					$this->db->setQuery($query);
					$result=$this->db->loadObject();
					$fields[$key]['suffix']=$result->sValue;
				}
			}
		}
		    	
		if(count($fields)==0){
        	IJReq::setResponseCode(204);
        }
		
        $se=0;
    	
        foreach($fields as $key=>$value){
    		$searchArray['search']['fields'][$se]['fid']=$value['fid'];
    		$searchArray['search']['fields'][$se]['caption']=(isset($value['suffix']) && !empty($value['suffix'])) ? $value['suffix'].':' : $value['sValue'];
			
			if($value['searchMethod']=='mselect' or $value['searchMethod']=='chbx'){
				$searchArray['search']['fields'][$se]['type']='multipleselect';
			}else if($value['fieldType']=='calendar'){
				$searchArray['search']['fields'][$se]['type']='date';
			}else{
				$searchArray['search']['fields'][$se]['type']=$value['searchMethod'];
			}
			
			$searchArray['search']['fields'][$se]['value']="";
			$fid=$value['fid'];
			$searchArray['search']['fields'][$se]['from']="";
			$searchArray['search']['fields'][$se]['to']="";
			
			if($value['searchMethod']=='range'){
				$searchArray['search']['fields'][$se]['name']=$value['nid'];
				$searchArray['search']['fields'][$se]['from']['name']='from';
				$searchArray['search']['fields'][$se]['to']['name']='to';
				$values=explode(",",$value['searchRangeValues']);
				$trimmed_array=array();
				foreach($values as $trke=>$trval){
					$trimmed_array[]=str_replace(' ','',$trval);					
				}
				$searchArray['search']['fields'][$se]['from']['value']=min($trimmed_array);  
				$searchArray['search']['fields'][$se]['to']['value']=max($trimmed_array);				
			}else if($value['fieldType']=='calendar'){
				$searchArray['search']['fields'][$se]['name']=$value['nid'];
				$searchArray['search']['fields'][$se]['from']['name']='from';
				$searchArray['search']['fields'][$se]['to']['name']='to';
				$searchArray['search']['fields'][$se]['from']['value']='';  
				$searchArray['search']['fields'][$se]['to']['value']='';
			}else{
				$searchArray['search']['fields'][$se]['name']=$value['nid'];
			}
			
			
			if($value['searchMethod']=='select'){
				//fetch options
				$query = "	SELECT DISTINCT baseData 
							FROM #__sobipro_field_data 
							WHERE fid={$fid} 
							AND section={$sectionID} 
							AND enabled=1";
				$this->db->setQuery($query);
				$values=$this->db->loadResultArray();
				$searchArray['search']['fields'][$se]['options'][0]['name']='Select '.$value['sValue'].'...';
				$searchArray['search']['fields'][$se]['options'][0]['value']='';
				
				if($values[0]){
					foreach($values as $ke=>$ve){
						$query="SELECT sValue   
								FROM #__sobipro_language 
								WHERE `sKey` LIKE '{$ve}'";
						$this->db->setQuery($query);
						$result=$this->db->loadResult();
						$searchArray['search']['fields'][$se]['options'][$ke+1]['name']=($result) ? trim($result) : trim($ve);
						$searchArray['search']['fields'][$se]['options'][$ke+1]['value']=trim($ve);
					}
				}else{
					$query="SELECT optValue 
							FROM #__sobipro_field_option 
							WHERE fid={$fid} 
							AND optParent=''  
							ORDER BY optPos";
					$this->db->setQuery($query);
					$groups=$this->db->loadResultArray();
					foreach($groups as $ke=>$ve){
						$query="SELECT optValue 
								FROM #__sobipro_field_option 
								WHERE fid={$fid} 
								AND optParent='{$ve}'  
								ORDER BY optPos";
						$this->db->setQuery($query);
						$values=$this->db->loadResultArray();
						if($values){
							foreach($values as $k=>$v){
								$query="SELECT sValue   
										FROM #__sobipro_language 
										WHERE `sKey` LIKE '{$v}'";
								$this->db->setQuery($query);
								$result=$this->db->loadResult();
								$xmlcnt["search"]["fields"][$se]["options"][$k+1]["name"] = ($result)  ? trim($ve).'-'.$result : trim($ve).'-'.$v;
								$xmlcnt["search"]["fields"][$se]["options"][$k+1]["value"]=trim($v);
							}
						}else{
							$query="SELECT sValue   
									FROM #__sobipro_language 
									WHERE `sKey` LIKE '{$ve}'";
							$this->db->setQuery($query);
							$result=$this->db->loadResult();
							$searchArray['search']['fields'][$se]['options'][$ke+1]['name']=($result) ? trim($result) : trim($ve);
							$searchArray['search']['fields'][$se]['options'][$ke+1]['value']=trim($ve);
						}
					}
				}
			}
			
			
			if($value["searchMethod"]=='chbx'){
				$query="SELECT optValue 
						FROM #__sobipro_field_option 
						WHERE fid={$fid} 
						ORDER BY optPos";
				$this->db->setQuery($query);
				$values=$this->db->loadResultArray();
				foreach($values as $ke=>$ve){
					$query="SELECT sValue   
							FROM #__sobipro_language 
							WHERE `sKey` LIKE '{$ve}'";
					$this->db->setQuery($query);
					$result=$this->db->loadResult();
					$searchArray['search']['fields'][$se]['options'][$ke]['name']=($result) ? trim($result) : trim($ve);
					$searchArray['search']['fields'][$se]['options'][$ke]['value']=trim($ve);
				}
			}
			
			if($value["searchMethod"]=='mselect'){
				$query="SELECT optValue 
						FROM #__sobipro_field_option 
						WHERE fid={$fid} 
						AND optParent=''  
						ORDER BY optPos";
				$this->db->setQuery($query);
				$groups=$this->db->loadResultArray();
				$i=0;
				foreach($groups as $ke=>$ve){
					$query="SELECT optValue 
							FROM #__sobipro_field_option 
							WHERE fid={$fid} 
							AND optParent like '{$ve}'  
							ORDER BY optPos";
					$this->db->setQuery($query);
					$values=$this->db->loadResultArray();
					if($values){
						foreach($values as $k=>$v){
							$query="SELECT sValue   
									FROM #__sobipro_language 
									WHERE `sKey` LIKE '{$v}'";
							$this->db->setQuery($query);
							$result=$this->db->loadResult();
							$searchArray['search']['fields'][$se]['options'][$i]['name']=($result) ? trim($ve).'-'.$result : trim($ve).'-'.$v;
							$searchArray['search']['fields'][$se]['options'][$i]['value']=trim($v);
							$i++;
						}
					}else{
						$query="SELECT sValue   
								FROM #__sobipro_language 
								WHERE `sKey` LIKE '{$ve}'
								AND fid={$fid}";
						$this->db->setQuery($query);
						$result=$this->db->loadResult();
						$searchArray['search']['fields'][$se]['options'][$ke]['name']=($result) ? trim($result) : trim($v);
						$searchArray['search']['fields'][$se]['options'][$ke]['value']=trim($ve);
					}
				}
			}$se++;
		}
		$jsonarray = array();
    	$jsonarray['code']		 = 200;
    	$jsonarray['search']     = $searchArray['search'];
		return $jsonarray;
    }
    /**
     * @uses This function is used to get radius search based on latitude,longitude and distance passed.
	 */
	private function radius_search($sid,$latfields){
		$this->latitude = $latfields['field_latitude'];
		$this->longitude = $latfields['field_longitude'];
		$this->distance = $latfields['field_distance'];
		
		if($this->distance && $this->latitude && $this->longitude){
			$cor = $this->getrad($this->longitude,$this->latitude,$this->distance);
			$query="SELECT sfg.sid 
					FROM #__sobipro_field_geo as sfg 
					WHERE sfg.section={$sid}
					AND sfg.latitude BETWEEN ".$cor['lat_min']." AND ".$cor['lat_max']." 
					AND sfg.longitude BETWEEN ".$cor['lng_min']." AND ".$cor['lng_max'];
			$this->db->setQuery($query);
			$ids = $this->db->loadResultArray();
			if(empty($this->_results)){
				$query="SELECT q1.id 
						FROM	(	SELECT so.*,sr.position   
									FROM #__sobipro_object as so 
									LEFT JOIN #__sobipro_relations as sr on sr.id=so.id 
									WHERE so.approved=1 
									AND so.oType='entry' 
									AND sr.oType='entry' 
									AND so.state=1 
								) as q1
						LEFT JOIN 	(
										SELECT sfd.sid,
										GROUP_CONCAT(sf.fid SEPARATOR ':::') as nid,
										GROUP_CONCAT(sf.fieldType SEPARATOR ':::') as fieldType,
										GROUP_CONCAT(sfd.baseData SEPARATOR ':::') as fieldValue,
										GROUP_CONCAT(sl.sValue SEPARATOR ':::') as fieldName 
										FROM #__sobipro_field as sf
										LEFT JOIN #__sobipro_field_data as sfd on sf.fid=sfd.fid 
										LEFT JOIN #__sobipro_language as sl on sf.fid=sl.fid 
										WHERE sf.enabled=1  
										AND sf.section={$sid} 
										AND sfd.section={$sid} 
										AND sfd.enabled=1 
										AND sl.sKey='name' 
										GROUP BY sfd.sid
									) as q2 ON q1.id=q2.sid";
				$this->db->setQuery($query);
				$this->_results = $this->db->loadResultArray();
			}
			
			foreach($this->_results as $key=>$value){
				if(!in_array($value,$ids)){
					unset($this->_results[$key]);
				}
			}
			$this->_results = array_unique($this->_results);
		}
	}
	
	private function getrad($longitude,$latitude,$radius){
		$cor=array();
		$cor["lng_min"] = $longitude - $radius / abs(cos(deg2rad($latitude)) * 69);
		$cor["lng_max"] = $longitude + $radius / abs(cos(deg2rad($latitude)) * 69);
		$cor["lat_min"] = $latitude - ($radius / 69);
		$cor["lat_max"] = $latitude + ($radius / 69);		
		return $cor;
	}
	
	
  	/**
     * @uses This function is used to get all entries of searchable fields satisfied. 
     * @example the json string will be like, : 
     *	{
	 *		"extName":"sobipro",
	 *		"extView":"isobipro",
 	 *		"extTask":"getsearch",
	 * 		"taskData":{
	 * 			"sectionID":"sectionID",
	 * 			"search_for":"search_for",
	 * 			"searchphrase":"searchphrase",
	 * 			"fields":"fields",
	 * 			"pageNO":"pageNO"
	 * 			}
	 * 	}
	 */
	function getsearch(){
		$sID= IJReq::getTaskData('sectionID');
		SPFactory::registry()->set( 'current_section',json_decode(json_encode($sID), FALSE));
		$keyword = IJReq::getTaskData('search_for');
		$ReqSearch = IJReq::getTaskData('fields');
		$ssid = SPRequest::cmd( 'ssid', SPRequest::cmd( 'ssid', null, 'cookie' ) );
		$pageNO = IJReq::getTaskData('pageNO');
    	$startFrom=($pageNO<=1) ? 0 : $itemLimit*($pageNO-1);
		
		$query="SELECT sf.fid,sf.nid,sf.params,sf.fieldType,sl.sValue  
				FROM #__sobipro_field as sf 
				LEFT JOIN #__sobipro_language as sl on sf.fid=sl.fid 
				WHERE sf.inSearch=1 
				AND sf.enabled=1
				AND sl.oType='field' 
				AND sl.sKey='name' 
				AND sf.section={$sID} 
				ORDER BY sf.position";
		$this->db->setQuery($query);
		$fields=$this->db->loadAssoclist();
		
		foreach($fields as $key=>$value){
			$raw = SPConfig::unserialize($value["params"]);
			if(!array_key_exists("searchMethod", $raw) or $raw["searchMethod"]=="general"){
				unset($fields[$key]);
			}else{
				unset($fields[$key]["params"]);
				foreach($raw as $r=>$p){
					$fields[$key][$r]=$p;
				}
			}
			
			/*
			 * fetch the suffix
			 */
			$query="SELECT sl.sKey,sl.sValue  
						FROM #__sobipro_language as sl 
						WHERE sl.fid={$value['fid']}";
			$this->db->setQuery($query);
			$lang=$this->db->loadObject();
			if($lang){
				preg_match('|\[|',$lang->sValue,$matches);
				if(isset($matches[0]) and $matches[0]){
					$srch=array('[',']');
					$lang->sValue=str_replace($srch,'',$lang->sValue);
					$tmp=explode(':',$lang->sValue);
					$tmp=explode('.',$tmp[1]);
					
					$query="SELECT sValue  
							FROM #__sobipro_config  
							WHERE cSection='{$tmp[0]}' 
							AND sKey='{$tmp[1]}'";
					$this->db->setQuery($query);
					$result=$this->db->loadObject();
					$fields[$key]['suffix']=$result->sValue;
				}
			}
		}
		
    	foreach($ReqSearch as $key=>$value){
			$value=get_object_vars($value);
			foreach($value as $ke=>$val){
				if(is_object($val)){
					$vall1=get_object_vars($val);
					$this->_request[$ke] = $vall1;
				}else{
    				$exobj1=explode(',',$val);
					$this->_request[$ke] = (count($exobj1)==1) ? implode(' ',$exobj1) : $exobj1;
    			}
			}
    	}
		
    	foreach($fields as $calke=>$calval){
			if(in_array($calval['nid'],array_keys($this->_request))){
				$inputForm[$calval['nid']]=$calval['inputForm'];
			}
		}
    	
		if($inputForm['field_deal_start']=='dd.mm.yy'){
    		$start_from_selector=date("d.m.Y H:i", strtotime($this->_request['field_deal_start']['from']));
    		$start_to_selector=date("d.m.Y H:i", strtotime($this->_request['field_deal_start']['to']));
		}else if($inputForm['field_deal_start']=='dd-mm-yy'){
			$start_from_selector=date("d-m-Y H:i", strtotime($this->_request['field_deal_start']['from']));
    		$start_to_selector=date("d-m-Y H:i", strtotime($this->_request['field_deal_start']['to']));
		}else if($inputForm['field_deal_start']=='mm/dd/yy'){
			$start_from_selector=date("m/d/Y H:i", strtotime($this->_request['field_deal_start']['from']));
    		$start_to_selector=date("m/d/Y H:i", strtotime($this->_request['field_deal_start']['to']));
		}else if($inputForm['field_deal_start']=='yy-mm-dd'){
			$start_from_selector=date("Y-m-d H:i", strtotime($this->_request['field_deal_start']['from']));
    		$start_to_selector=date("Y-m-d H:i", strtotime($this->_request['field_deal_start']['to']));
		}else if($inputForm['field_deal_start']=='yy.mm.dd'){
			$start_from_selector=date("Y.m.d H:i", strtotime($this->_request['field_deal_start']['from']));
    		$start_to_selector=date("Y.m.d H:i", strtotime($this->_request['field_deal_start']['to']));
		}else if($inputForm['field_deal_start']=='d.m.yy'){
			$start_from_selector=date("d.m.Y H:i", strtotime($this->_request['field_deal_start']['from']));
    		$start_to_selector=date("d.m.Y H:i", strtotime($this->_request['field_deal_start']['to']));
		}
		
		$this->_request['field_deal_start_from_selector'] = $start_from_selector;
		$this->_request['field_deal_start_to_selector'] = $start_to_selector;
		$this->_request['field_deal_start']['from'] = gmmktime(gmdate('H',strtotime($start_from_selector)), gmdate('i',strtotime($start_from_selector)), null, gmdate('m',strtotime($start_from_selector)), gmdate('d',strtotime($start_from_selector)), gmdate('Y',strtotime($start_from_selector))  );
		$this->_request['field_deal_start']['from']=$this->_request['field_deal_start']['from']*1000;
		$this->_request['field_deal_start']['to'] = gmmktime(gmdate('H',strtotime($start_to_selector)), gmdate('i',strtotime($start_to_selector)), null, gmdate('m',strtotime($start_to_selector)), gmdate('d',strtotime($start_to_selector)), gmdate('Y',strtotime($start_to_selector))  );
		$this->_request['field_deal_start']['to']=$this->_request['field_deal_start']['to']*1000;
		
		if($inputForm['field_deal_end']=='dd.mm.yy'){
			$end_from_selector=date("d.m.Y H:i", strtotime($this->_request['field_deal_end']['from']));
			$end_to_selector=date("d.m.Y H:i", strtotime($this->_request['field_deal_end']['to']));
		}else if($inputForm['field_deal_end']=='dd-mm-yy'){
			$end_from_selector=date("d-m-Y H:i", strtotime($this->_request['field_deal_end']['from']));
			$end_to_selector=date("d-m-Y H:i", strtotime($this->_request['field_deal_end']['to']));
		}else if($inputForm['field_deal_end']=='mm/dd/yy'){
			$end_from_selector=date("m/d/Y H:i", strtotime($this->_request['field_deal_end']['from']));
			$end_to_selector=date("m/d/Y H:i", strtotime($this->_request['field_deal_end']['to']));
		}else if($inputForm['field_deal_end']=='yy-mm-dd'){
			$end_from_selector=date("Y-m-d H:i", strtotime($this->_request['field_deal_end']['from']));
			$end_to_selector=date("Y-m-d H:i", strtotime($this->_request['field_deal_end']['to']));
		}else if($inputForm['field_deal_end']=='yy.mm.dd'){
			$end_from_selector=date("Y.m.d H:i", strtotime($this->_request['field_deal_end']['from']));
			$end_to_selector=date("Y.m.d H:i", strtotime($this->_request['field_deal_end']['to']));
		}else if($inputForm['field_deal_end']=='d.m.yy'){
			$end_from_selector=date("d.m.Y H:i", strtotime($this->_request['field_deal_end']['from']));
			$end_to_selector=date("d.m.Y H:i", strtotime($this->_request['field_deal_end']['to']));
		}
		
		$this->_request['field_deal_end_from_selector'] = $end_from_selector;
		$this->_request['field_deal_end_to_selector'] = $end_to_selector;
		$this->_request['field_deal_end']['from'] = gmmktime(gmdate('H',strtotime($end_from_selector)), gmdate('i',strtotime($end_from_selector)), null, gmdate('m',strtotime($end_from_selector)), gmdate('d',strtotime($end_from_selector)), gmdate('Y',strtotime($end_from_selector))  );
		$this->_request['field_deal_end']['from']=$this->_request['field_deal_end']['from']*1000;
    	$this->_request['field_deal_end']['to'] = gmmktime(gmdate('H',strtotime($end_to_selector)), gmdate('i',strtotime($end_to_selector)), null, gmdate('m',strtotime($end_to_selector)), gmdate('d',strtotime($end_to_selector)), gmdate('Y',strtotime($end_to_selector))  );
		$this->_request['field_deal_end']['to']=$this->_request['field_deal_end']['to']*1000;
		
    	$latfields = $this->_request;
    	$this->_request[ 'search_for' ] = str_replace( '*', '%', IJReq::getTaskData('search_for'));
    	$this->_request[ 'phrase' ] = IJReq::getTaskData('searchphrase',Sobi::Cfg( 'search.searchphrase', 'any' ));
    	$this->_fields = $this->loadFields($sID);
		$searchForString = false;
		Sobi::Trigger( 'OnRequest', 'Search', array( &$this->_request ) );
		usort( $this->_fields, array( 'self', 'sortByPrio' ) );
    	if( strlen( $this->_request[ 'search_for' ] ) && $this->_request[ 'search_for' ] != Sobi::Txt( 'SH.SEARCH_FOR_BOX' ) ) {
			$searchForString = true;
			switch ( $this->_request[ 'phrase' ] ) {
				case 'all':
				case 'any':
					$this->searchWords( ( $this->_request[ 'phrase' ] == 'all' ),$sID );
					break;
				case 'exact': 
					$this->searchPhrase($sID);
					break;
			}
			$this->_results = array_unique( $this->_results );
		}
		Sobi::Trigger( 'AfterBasic', 'Search', array( &$this->_results ));
    	/*
		 * radius search
		 */
		if($this->_results){
			$this->radius_search($sID,$latfields);
		}else if($keyword == ''){
			$this->radius_search($sID,$latfields);
		}
		/* ... now the extended search. Check which data we've recieved */
		if( count( $this->_fields ) ) {
			$results = null;
			foreach ( $this->_fields as $field ) {
				if(
					isset( $this->_request[ $field->get( 'nid' ) ] )
					&& ( $this->_request[ $field->get( 'nid' ) ] != null )
				) {
					$fr = $field->searchData( $this->_request[ $field->get( 'nid' ) ], $sID );
					/* if we didn't got any results before this array contains the results */
					if( !( is_array( $results ) ) ) {
						$results = $fr;
					}
					/* otherwise intersect these two arrays */
					else {
						if( is_array( $fr ) ) {
							$results = array_intersect( $results, $fr );
						}
					}
				}
			}
			if( is_array( $results ) ) {
				/* if we had also a string to search we have to get the intersection */
				if( $searchForString ) {
					$this->_results = array_intersect( $this->_results, $results );
				}
				/* otherwise THESE are the results */
				else {
					$this->_results = $results;
				}
			}
		}
		
		$this->verify();
		$field_rating = $this->_request['field_rating'];
		if($this->_results && $field_rating){
			$query="SELECT sid 
					FROM #__sobipro_sprr_rating 
					WHERE vote={$field_rating} 
					AND state='1'";
			$this->db->setQuery($query);
			$ratingItemID = $this->db->loadResultArray();
			foreach($this->_results as $key=>$value){
				if(!in_array($value,$ratingItemID)){
					unset($this->_results[$key]);
				}
			}
			$this->_results = array_unique($this->_results);
		}else if($this->_results){
			$this->_results = $this->_results;
		}else {
			$query="SELECT sid 
					FROM #__sobipro_sprr_rating 
					WHERE vote={$field_rating} 
					AND state='1'";
			$this->db->setQuery($query);
			$this->_results = $this->db->loadResultArray();
			$this->_results = array_unique($this->_results);
		}
		$searchentries = null;
		$total_count = count($this->_results);
		foreach($this->_results as $ke=>$val){
			$query="SELECT parent 
					FROM #__sobipro_object 
					WHERE id={$val} 
					AND oType='entry'";
			$this->db->setQuery($query);
			$parent = $this->db->loadResult();
			$searchentries.=($searchentries) ? ",".$val.":".$sID.":".$parent : $val.":".$sID.":".$parent;
		}
		
		$res = ( is_array( $this->_results ) && count( $this->_results ) ) ? implode( ', ', $this->_results ) : null;
		$response = $this->getResults($this->_results);
   		
		if($total_count>0){
   			$jsonarray['code'] = 200;
   		}else{
   			$jsonarray['code'] = 204;
   			IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
   			return false;
   		}
   		$jsonarray['total'] = $total_count;
   		$jsonarray['pageLimit']	= $itemLimit;
   		$jsonarray = array_merge(array('code' => $jsonarray['code'],'total' => $jsonarray['total'],'pageLimit' => $jsonarray['pageLimit']),$response);
		return $jsonarray;
    }
    
    
	private function verify(){
		if( $this->_results ) {
			if(!$this->IJUserID) {
				$condi ="	AND state ='1' 
							AND (	(	validUntil > NOW() 
										OR validUntil IN ( '0000-00-00 00:00:00', '1970-01-01 00:00:00' ) ) 
								AND ( 	validSince < NOW() 
										OR validSince IN( '0000-00-00 00:00:00', '1970-01-01 00:00:00' ) )   
								)";
			}else{
				$condi ="";
			}
			
			foreach($this->_results as $key=>$value){
				$query="SELECT id 
						FROM #__sobipro_object 
						WHERE id={$value} 
						AND (oType ='entry') {$condi}";
				$this->db->setQuery($query);
				$temp=$this->db->loadResult();
				if(!$temp){
					unset($this->_results[$key]);
				}
			}
			Sobi::Trigger( 'OnVerify', 'Search', array( &$this->_results ) );
		}
	}
	
	
	function getResults($entryIDs){
		$sectionID  = IJReq::getTaskData('sectionID',0);
    	$query="SELECT so.name
				FROM #__sobipro_object as so 
				WHERE so.oType='section'
    			AND so.id={$sectionID}";
    	$this->db->setQuery($query);
        $sectionName = $this->db->loadResult();
    	SPFactory::registry()->set( 'current_section',json_decode(json_encode($sectionID), FALSE));
    	defined( 'SOBI_ACL' ) || define( 'SOBI_ACL', 'front' );
    	
    	if (file_exists(JPATH_SITE .'/components/com_sobipro/lib/models/review.php')) {
    		$tmpreviewobject = new tmprating();
    	}
    	$sortBy=IJReq::getTaskData('sortBy');
    	$sortOrder=IJReq::getTaskData('sortOrder');
    	$filterBy=IJReq::getTaskData('filterBy');
    	$FilterBy = explode(',',$filterBy);
    	$featuredFirst = IJReq::getTaskData('featuredFirst');
    	$pageNO = IJReq::getTaskData('pageNO');
    	$entryLimit= SEARCH_LIMIT;
		$cfginstanse = SPConfig::getInstance();
    	$cfginstanse->addIniFile( 'etc.config', true );
        $cfginstanse->addTable( 'spdb_config', $sectionID );
        /* initialise interface config setting */
        $apmainframe = & SPFactory::mainframe();
        $apmainframe->getBasicCfg();
    	$cOrder = $this->parseOrdering( 'categories', 'corder', $this->tKey( 'view', 'categories_ordering', Sobi::Cfg( 'list.categories_ordering', 'name.asc' ) ) );
    	$cLim = $this->tKey( 'view', 'categories_limit', -1 );
		$eLimit = $this->tKey( 'view', 'entries_limit', Sobi::Cfg( 'list.entries_limit', 2 ) );
    	if($entryLimit){
    	 	$itemLimit=$entryLimit;
    	}
    	$eLimStart= ($pageNO<=1) ? 0 : $itemLimit*($pageNO-1);
    	
    	$SPSectionCtrl = new SPSectionCtrl();
    	$SPSectionCtrl->setModel('SPCategory');
    	$obj = SPFactory::object( $sectionID );
    	$SPSectionCtrl->extend($obj);
    	$catid = $SPSectionCtrl->getCats( $cOrder, $cLim );
    	$si = SPSection::getInstance($sectionID);
    	
        foreach ($entryIDs as $enid){
        	$entry = SPFactory::Entry( $enid );
        	$en[ 'id' ] = $entry->get( 'id' );
            $en[ 'nid' ] = $entry->get( 'nid' );
            $en[ 'name' ] = array(
                '_complex' => 1,
                '_data' => $entry->get( 'name' ),
                '_attributes' => array( 'lang' => Sobi::Lang( false ) )
            );
            $en[ 'url_array' ] = array( 'title' => $entry->get( 'name' ), 'pid' => $entry->get( 'primary' ), 'sid' => $entry->get( 'id' ) );
            if ( strstr( SPRequest::task(), 'search' ) || $noId || ( Sobi::Cfg( 'section.force_category_id', false ) && SPRequest::sid() == Sobi::Section() ) ) {
                $en[ 'url' ] = Sobi::Url( array( 'title' => $entry->get( 'name' ), /*'task' => 'entry.details',*/
                    'pid' => $entry->get( 'primary' ), 'sid' => $entry->get( 'id' ) ) );
            }else {
                $en[ 'url' ] = Sobi::Url( array( 'title' => $entry->get( 'name' ), /*'task' => 'entry.details',*/
                    'pid' => SPRequest::sid(), 'sid' => $entry->get( 'id' ) ) );
            }
            
            if ( Sobi::Cfg( 'list.entry_meta', true ) ) {
                $en[ 'meta' ] = array(
                    'description' => $entry->get( 'metaDesc' ),
                    'keys' => $this->metaKeys( $entry ),
                    'author' => $entry->get( 'metaAuthor' ),
                    'robots' => $entry->get( 'metaRobots' ),
                );
            }
            
            if ( $manager || ( ( Sobi::My( 'id' ) && ( Sobi::My( 'id' ) == $entry->get( 'owner' ) ) && Sobi::Can( 'entry', 'edit', 'own', Sobi::Section() ) ) ) ) {
                $en[ 'edit_url' ] = Sobi::Url( array( 'task' => 'entry.edit', 'pid' => SPRequest::sid(), 'sid' => $entry->get( 'id' ) ) );
            }else if ( isset( $en[ 'edit_url' ] ) ) {
                unset( $en[ 'edit_url' ] );
            }
            $en[ 'edit_url_array' ] = array( 'task' => 'entry.edit', 'pid' => SPRequest::sid(), 'sid' => $entry->get( 'id' ) );
            $en[ 'created_time' ] = $entry->get( 'createdTime' );
            $en[ 'updated_time' ] = $entry->get( 'updatedTime' );
            $en[ 'valid_since' ] = $entry->get( 'validSince' );
            $en[ 'valid_until' ] = $entry->get( 'validUntil' );
            if ( $entry->get( 'state' ) == 0 ) {
                $en[ 'state' ] = 'unpublished';
            }else if ( strtotime( $entry->get( 'validUntil' ) ) != 0 && strtotime( $entry->get( 'validUntil' ) ) < time() ) {
                $en[ 'state' ] = 'expired';
            }else if ( strtotime( $entry->get( 'validSince' ) ) != 0 && strtotime( $entry->get( 'validSince' ) ) > time() ) {
                $en[ 'state' ] = 'pending';
            }else {
                $en[ 'state' ] = 'published';
            }
            $en[ 'author' ] = $entry->get( 'owner' );
            $en[ 'counter' ] = $entry->get( 'counter' );
            $en[ 'approved' ] = $entry->get( 'approved' );
            //		$en[ 'confirmed' ] = $entry->get( 'confirmed' );
            if ( Sobi::Cfg( 'list.entry_cats', true ) ) {
                $cats = $entry->get( 'categories' );
                $categories = array();
                if ( count( $cats ) ) {
                    $cn = SPLang::translateObject( array_keys( $cats ), 'name' );
                }
                foreach ( $cats as $cid => $cat ) {
                    $categories[ ] = array(
                        '_complex' => 1,
                        '_data' => SPLang::clean( $cn[ $cid ][ 'value' ] ),
                        '_attributes' => array( 'lang' => Sobi::Lang( false ), 'id' => $cat[ 'pid' ], 'position' => $cat[ 'position' ], 'url' => Sobi::Url( array( 'sid' => $cat[ 'pid' ], 'title' => $cat[ 'name' ] ) ) )
                    );
                }
                $en[ 'categories' ] = $categories;
            }
            $fields = $entry->getFields();
           
        	$f = array();
			if( count( $fields ) ) {
				foreach ( $fields as $field ) {
					if( $field->enabled( 'details' ) && $field->get( 'id' ) != Sobi::Cfg( 'entry.name_field' ) ) {
						$struct = $field->struct();
						$options = null;
						if( isset( $struct[ '_options' ] ) ) {
							$options = $struct[ '_options' ];
							unset( $struct[ '_options' ] );
						}
						$f[ $field->get( 'nid' ) ] = array(
							'_complex' => 1,
							'_data' => array(
									'label' => array(
										'_complex' => 1,
										'_data' => $field->get( 'name' ),
										'_attributes' => array( 'lang' => Sobi::Lang( false ), 'show' => $field->get( 'withLabel' ) )
									),
									'data' => $struct,
							),
							'_attributes' => array( 'id' => $field->get( 'id' ), 'type' => $field->get( 'type' ), 'suffix' => $field->get( 'suffix' ), 'position' => $field->get( 'position' ), 'css_class' => ( strlen( $field->get( 'cssClass' ) ) ? $field->get( 'cssClass' ) : 'spField' ) )
						);
						if( Sobi::Cfg( 'entry.field_description', false ) ) {
							$f[ $field->get( 'nid' ) ][ '_data' ][ 'description' ] = array( '_complex' => 1, '_xml' => 1, '_data' => $field->get( 'description' ) );
						}
						if( $options ) {
							$f[ $field->get( 'nid' ) ][ '_data' ][ 'options' ] = $options;
						}
						if( isset( $struct[ '_xml_out' ] ) && count( $struct[ '_xml_out' ] ) ) {
							foreach( $struct[ '_xml_out' ] as $k => $v )
								$f[ $field->get( 'nid' ) ][ '_data' ][ $k ] = $v;
						}
					}
				}
				$en[ 'fields' ] = $f;
				//
				if (file_exists(JPATH_SITE .'/components/com_sobipro/lib/models/review.php')) {
					$tmpreviewobject->setSid($en[ 'id' ]);
		    		$entry=array();
		    		$entry[ 'entry' ][ '_data' ][ 'name' ][ '_data' ] = $en['name']['_data'];
		    		$query="SELECT count(rid)   
						FROM #__sobipro_sprr_review  
						WHERE sid={$en[ 'id' ]}"; 
					$this->db->setQuery($query);
					$totalreview = $this->db->loadResult();
					$tmpreviewobject->revOnSite = $totalreview;
			    	$details = $tmpreviewobject->setDetails($entry, $site = 1);
			    	$en['reviews'] = round($entry['reviews']['summary_rating']['overall']['_attributes']['value']);
				}
				//
				$entries[]=$en;
			}
        }
        $namearray = array();
		foreach($entries as $namek=>$nval){
			$total_categories=$nval['categories'];
        	$categoryNames = array();
        	foreach($total_categories as $feke=>$feval){
        		$categoryNames[]=$feval['_data'];
        	}
        	if(in_array('Featured',$categoryNames)){
				$featureCatIds[] = $nval;
			} else {
				$catIds[] = $nval;
			}
        }
        
        if($featuredFirst=='Yes') {
        	if($featureCatIds!='' && $catIds!=''){
        		$entries = array_merge($featureCatIds,$catIds);
        	}else if($featureCatIds==''){
        		$entries = $catIds;
        	}else{
        		$entries = $featureCatIds;
        	}
        }
        
        foreach($entries as $namek=>$nval){
        	$fieldprices[]=$nval['fields']['field_price']['_data']['data']['_data'];
        	$namearray[]=$nval['name']['_data'];
        	$averagerating[]=$nval['reviews'];
        }

        if($sortBy=='title'){
	    	switch ($sortOrder){
	            case 'asc' :
	                array_multisort($namearray, SORT_ASC, $entries);
	                break;
	
	            case 'desc' :
	              	array_multisort($namearray, SORT_DESC, $entries);
	                break;
			}
        }
        
    	if($sortBy=='rating'){
	    	switch ($sortOrder){
	            case 'asc' :
	                array_multisort($averagerating, SORT_ASC, $entries);
	                break;
	
	            case 'desc' :
	              	array_multisort($averagerating, SORT_DESC, $entries);
	                break;
	        }
        }
        
    	if($sortBy=='price'){
	    	switch ($sortOrder){
	            case 'asc' :
	                array_multisort($fieldprices, SORT_ASC, $entries);
	                break;
	
	            case 'desc' :
	              	array_multisort($fieldprices, SORT_DESC, $entries);
	                break;
	        }
        }
        
        if($filterBy!="" && !($catID)){
	    	$new = array();
	    	foreach($averagerating as $avKey=>$avVal){
	    		if(in_array($avVal,$FilterBy)){
	    			$new[$avKey]=$avVal;
	    		}else{
	    			unset($averagerating[$avKey]);
	    		}
	    	}
	    	
			foreach($new as $newkey=>$newval){
				$c[] = $entries[$newkey];
			}
			$entries=$c;
    	}
    	
    	$totalEntries = count($entryIDs);
		
    	$looplimit = (count($entries)<($itemLimit*$pageNO)) ? count($entries) : ($itemLimit*$pageNO);
    
    	$incr=0;
      	for($inc=$eLimStart;$inc<$looplimit;$inc++){
			$entryID=$entries[$inc]['id'];
			$entryArray['entries'][$incr]['id'] = $entryID;
			$entryArray['entries'][$incr]['sharelink'] = $_SERVER['SERVER_ADDR'].$entries[$inc]['url'];
			$Title=$entries[$inc]['name']['_data'];
			$entryArray['entries'][$incr]['title'] = $Title;
			$query="SELECT fid,latitude,longitude   
					FROM #__sobipro_field_geo 
					WHERE section={$sectionID} 
					AND sid={$entryID}";
			$this->db->setQuery($query);
			$mapfields = $this->db->loadObject();
			$entryArray['entries'][$incr]['latitude']=($mapfields->latitude) ? $mapfields->latitude : "";
			$entryArray['entries'][$incr]['longitude']=($mapfields->longitude) ? $mapfields->longitude : "";
			$fields = $entries[$inc]['fields'];
			if (file_exists(JPATH_SITE .'/components/com_sobipro/lib/models/review.php')) {
				$tmpreviewobject->setSid($entryID);
	    		$entry=array();
	    		$entry[ 'entry' ][ '_data' ][ 'name' ][ '_data' ] = $Title;
		    	$query="SELECT count(rid)   
						FROM #__sobipro_sprr_review  
						WHERE sid={$entryID}"; 
				$this->db->setQuery($query);
				$totalreview = $this->db->loadResult();
				$tmpreviewobject->revOnSite = $totalreview;
		    	$details = $tmpreviewobject->setDetails($entry, $site = 1);
		    	$reviews = $entry['reviews'];
		    	$over=$entry['reviews']['summary_rating'];
		    	$fieldslabel=$entry['reviews']['summary_rating']['fields'];
			}
	    	if($reviews && $reviews['summary_rating']){	
	    		unset($reviews['summary_rating']);unset($reviews['navigation']);
				for($re=0;$re<count($reviews);$re++) {
		    		$positives=implode(",",$reviews[$re]['_data']['input']['positives']);
		    		$negatives=implode(",",$reviews[$re]['_data']['input']['negatives']);
		    		$entryArray['entries'][$incr]['reviewrating'][$re]['reviewid']=$reviews[$re]['_attributes']['id'];
		    		$entryArray['entries'][$incr]['reviewrating'][$re]['reviewtitle']=$reviews[$re]['_data']['title'];
		    		$entryArray['entries'][$incr]['reviewrating'][$re]['review']=$reviews[$re]['_data']['input']['text']['_data'];
		    		$entryArray['entries'][$incr]['reviewrating'][$re]['reviewpositives']=($positives!='') ? $positives : '';
		    		$entryArray['entries'][$incr]['reviewrating'][$re]['reviewnegatives']=($negatives!='') ? $negatives : '';
		    		$entryArray['entries'][$incr]['reviewrating'][$re]['reviewdate']=$reviews[$re]['_attributes']['date'];
		    		$entryArray['entries'][$incr]['reviewrating'][$re]['reviewusername']=($reviews[$re]['_data']['author']['_data']!='') ? $reviews[$re]['_data']['author']['_data'] : '';
		    		$entryArray['entries'][$incr]['reviewrating'][$re]['reviewuserid']=$reviews[$re]['_data']['author']['_attributes']['id'];	
		    		$ratingsArray=$reviews[$re]['_data']['ratings'];
		    		for($ratcount=0;$ratcount<count($ratingsArray);$ratcount++){
			    		$entryArray['entries'][$incr]['reviewrating'][$re]['ratings'][$ratcount]['ratingid']=$reviews[$re]['_data']['ratings'][$ratcount]['_attributes']['id'];	
			    		$entryArray['entries'][$incr]['reviewrating'][$re]['ratings'][$ratcount]['ratingvote']=$reviews[$re]['_data']['ratings'][$ratcount]['_data'];
			    		$entryArray['entries'][$incr]['reviewrating'][$re]['ratings'][$ratcount]['criterionname']=$reviews[$re]['_data']['ratings'][$ratcount]['_attributes']['label'];
		    		}
	    			$entryArray['entries'][$incr]['reviewrating'][$re]['averagerating']=$reviews[$re]['_attributes']['oar'];
				}
	    	}else{
	    		$entryArray['entries'][$incr]['reviewrating']='';
	    	}
	    	
	    	if($fieldslabel!=''){
				foreach($fieldslabel as $f=>$flabel){
					$entryArray['entries'][$incr]['criterionaverage'][$f]['criterionname']=$flabel['_attributes']['label'];
					$entryArray['entries'][$incr]['criterionaverage'][$f]['ratingvote']=$flabel['_attributes']['value'];
				}
	    	}else{
	    		$entryArray['entries'][$incr]['criterionaverage']='';
	    	}
	    	
	    	$entryArray['entries'][$incr]['averagerating']=($over['overall']['_attributes']['value']) ? round($over['overall']['_attributes']['value']) : 0;
	    	$entryArray['entries'][$incr]['totalreviewcount']=($over['overall']['_attributes']['count']) ? $over['overall']['_attributes']['count'] : 0;
			
	    	foreach($fields as $fk=>$fv){
	    		$fieldID = $fv['_attributes']['id'];
	    		$fieldIDs[]=$fieldID;
	    	}
	    	$Types=$this->gettypes($fieldIDs);
	    	
	    	$entryArray['entries'][$incr]['img_galleries']='';
			$i=0;
			foreach($fields as $fkey=>$fval){
				$caption = $fval['_data']['label']['_data'];
				$type    = $fval['_attributes']['type'];
				$value   = $fval['_data']['data']['_data'];
				$fieldID = $fval['_attributes']['id'];
				
				$query="SELECT sValue   
						FROM #__sobipro_config  
						WHERE cSection='payments' 
						AND sKey='currency'"; 
				$this->db->setQuery($query);
				$currency = $this->db->loadResult();
				
				$query="SELECT sl.sValue  
						FROM #__sobipro_language as sl 
						WHERE sl.fid={$fieldID}
						AND sl.sKey='suffix'";
				$this->db->setQuery($query);
				$unit=$this->db->loadResult();
				
				if(($caption=='Company Image' || $caption=='Image') && isset($value['img']['_attributes']['src'])){
					$entryArray['entries'][$incr]['img_galleries'][]=$value['img']['_attributes']['src'];
				}else if(isset($unit) && $unit!='[cfg:payments.currency]'){
					$entryArray['entries'][$incr]['field'][$i]['unit']=$unit;
				}else if($unit=='[cfg:payments.currency]'){
					$entryArray['entries'][$incr]['field'][$i]['unit']=$currency;
				}else{
					$entryArray['entries'][$incr]['field'][$i]['unit']='';
				}
	
				$entryArray['entries'][$incr]['field'][$i]['type']=$type;
				foreach($Types as $TypeValues){
		    		foreach($TypeValues as $tyke=>$tyval){
		    			if($fieldID==$tyke){
		    				$entryArray['entries'][$incr]['field'][$i]['type']=$tyval;
		    			}
		    		}
		    	}
			    	
				$entryArray['entries'][$incr]['field'][$i]['labelid']=$fkey;
				if($type=='url'){
					$webTitle=$value['a']['_data'];
					$entryArray['entries'][$incr]['field'][$i]['caption']=(isset($webTitle)) ? $webTitle : '';
				}else{
					$entryArray['entries'][$incr]['field'][$i]['caption']=$caption;
				}
				
				if($type=='image' && $caption=='Company Logo'){
					$Images=$value['img']['_attributes']['src'];
					$entryArray['entries'][$incr]['field'][$i]['value']=($Images!="") ? $Images : '';
				}else if($type=='chbxgroup' || $type=='multiselect'){
					$days=$value['ul']['_data'];
					foreach($days as $dke=>$dval){
						$implodeval[$dke]= $dval['_value'];
					}
					$entryArray['entries'][$incr]['field'][$i]['value']=implode(',',$implodeval );
				}else if($type=='url'){
					$webValue=$value['a']['_attributes']['href'];
					$entryArray['entries'][$incr]['field'][$i]['value']=(isset($webValue)) ? $webValue : '';
				}else if($type=='geomap'){
					unset($entryArray['entries'][$incr]['field'][$i]);
				}else if($value){
					$entryArray['entries'][$incr]['field'][$i]['value']=$value;
				}else{
					$entryArray['entries'][$incr]['field'][$i]['value']="";
				}
				$i++;
				$entryArray['entries'][$incr]['field'] = array_values($entryArray['entries'][$incr]['field']);
			}
			$incr++;
		}
		
    	$jsonarray = array();
		$jsonarray['total']		 = $totalEntries;
		$jsonarray['pageLimit']	 = $itemLimit;
		$jsonarray['sectionid']	 = $sectionID;
		$jsonarray['sectionname']=$sectionName;
		$jsonarray['catid']	     = "";
		$jsonarray['categories'] = "";
		$jsonarray['entries']=($entryArray['entries']) ? $entryArray['entries'] : "";
		return $jsonarray;
	}
	
	function loadFields($sid){
		$fields = null;
		$fmod = SPLoader::loadModel( 'field' );
		 
		/* get fields */
       	$query="SELECT * 
       			FROM #__sobipro_field 
       			WHERE section={$sid} 
       			AND inSearch=1 
       			AND enabled=1 
       			ORDER BY position";
       	$this->db->setQuery($query);
       	$fields = $this->db->loadObjectList();
      	
        if( count( $fields ) ) {
        	foreach ( $fields as $i => $f ) {
        		/* @var SPField $field */
        		$field = new $fmod();
        		$field->extend( $f );
        		if( count( $this->_request ) && isset( $this->_request[ $field->get( 'nid' ) ] ) ) {
        			$field->setSelected( $this->_request[ $field->get( 'nid' ) ] );
        		}
        		$fields[ $i ] = $field;
        	}
        }
        Sobi::Trigger( 'LoadField', 'Search', array( &$fields ) );
        return $fields;
	}
	
	function sortByPrio( $obj, $to ){
		return ( $obj->get( 'priority' ) == $to->get( 'priority' ) ) ? 0 : ( ( $obj->get( 'priority' ) < $to->get( 'priority' ) ) ? -1 : 1 );
	}
	
	function searchWords( $all,$sid ){
		/* @TODO categories */
		$matches = array();
		/* extrapolate single words */
		preg_match_all( Sobi::Cfg( 'search.word_filter', '/\w+|%/' ), $this->_request[ 'search_for' ], $matches );
		if( count( $matches ) && isset( $matches[ 0 ] ) ) {
			$wordResults = array();
			$results = null;
			/* search all fields for this word */
			foreach ( $matches[ 0 ] as $word ) {
				$wordResults[ $word ] = $this->travelFields( $word,false,$sid );
			}
			if( count( $wordResults ) ) {
				foreach ( $wordResults as $wordResult ) {
					if( is_null( $results ) ) {
						$results = $wordResult;
					}else if( $all && is_array( $wordResult ) ) {
						$results = array_intersect( $results, $wordResult );
					}else if( is_array( $wordResult ) ) {
						$results = array_merge( $results, $wordResult );
					}
				}
			}
			$this->_results = $results;
		}
	}
	
	function searchPhrase($sid){
		/* @TODO categories */
		$search = str_replace( '.', '\.', $this->_request[ 'search_for' ] );
		$this->_results = $this->travelFields( "REGEXP:[[:<:]]{$search}[[:>:]]", true, $sid );
	}
	
	function travelFields( $word, $regex = false, $sid ){
		$results = array();
		if( count( $this->_fields ) ) {
			foreach( $this->_fields as $field ) {
				$fr = $field->searchString( $word, $sid, $regex );
				if( is_array( $fr ) && count( $fr ) ) {
					$results = array_merge( $results, $fr );
				}
			}
		}
		return $results;
	}
	
	
	/**
     * @uses This function is used to add review-rating in particular entry.
     * @example the json string will be like, : 
     *	{
	 *		"extName":"sobipro",
	 *		"extView":"isobipro",
 	 *		"extTask":"addreview",
	 * 		"taskData":{
	 * 			"form":"form",(1/0)
	 * 			"section":"section",(show addreview form of particular sectionid passed)
	 * 			"rating":"rating",(array)
	 * 			"review":"review"(array)
	 * 			}
	 * 	}
	 */
	function addreview(){
		$section=IJReq::getTaskData('section');
		$sid=IJReq::getTaskData('sid');
		$form=IJReq::getTaskData('form');
	
    	$jsonarray = array();
		if($form){
			$query="SELECT `value` 
					FROM #__sobipro_registry 
					WHERE `section`='sprr_{$section}' 
					AND `key`='revPositive'";
			$this->db->setQuery($query);
			$positive_enabled = $this->db->loadResult();
			$query="SELECT `value` 
					FROM #__sobipro_registry 
					WHERE `section`='sprr_{$section}' 
					AND `key`='revMailRequ'";
			$this->db->setQuery($query);
			$mail_required = $this->db->loadResult();
			$jsonarray['code']		 = 200;		
			$i=0;
			$j=1;
			$jsonarray ["fields"] [$i] ["field"] ["id"] = $j;
			$jsonarray ["fields"] [$i] ["field"] ["title"] = "title";
			$jsonarray ["fields"] [$i] ["field"] ["type"] = "text";
			$jsonarray ["fields"] [$i] ["field"] ["required"] = 1;
			$jsonarray ["fields"] [$i] ["field"] ["caption"] = "Title";
			$jsonarray ["fields"] [$i] ["field"] ["value"] = "";
			$i++;
			$j++;
			
			$jsonarray ["fields"] [$i] ["field"] ["id"] = $j;
			$jsonarray ["fields"] [$i] ["field"] ["title"] = "review";
			$jsonarray ["fields"] [$i] ["field"] ["type"] = "textarea";
			$jsonarray ["fields"] [$i] ["field"] ["required"] = 1;
			$jsonarray ["fields"] [$i] ["field"] ["caption"] = "Review";
			$jsonarray ["fields"] [$i] ["field"] ["value"] = "";
			$i++;
			$j++;
			$criteriaFields=$this->reviewFields($section);	
			foreach($criteriaFields as $field){
				$jsonarray ["fields"] [$i] ["field"] ["id"] = $j;
				$jsonarray ["fields"] [$i] ["field"] ["title"] = $field["fid"];
				$jsonarray ["fields"] [$i] ["field"] ["type"] = "select";
				$jsonarray ["fields"] [$i] ["field"] ["required"] = 0;
				$jsonarray ["fields"] [$i] ["field"] ["caption"] = $field["label"];
				$jsonarray ["fields"] [$i] ["field"] ["value"] = "";
				$i++;
				$j++;
			}
			
			if($positive_enabled){
				$jsonarray ["fields"] [$i] ["field"] ["id"] = $j;
				$jsonarray ["fields"] [$i] ["field"] ["title"] = "pos_review";
				$jsonarray ["fields"] [$i] ["field"] ["type"] = "textarea";
				$jsonarray ["fields"] [$i] ["field"] ["required"] = 0;
				$jsonarray ["fields"] [$i] ["field"] ["caption"] = "I Like";
				$jsonarray ["fields"] [$i] ["field"] ["value"] = "";
				$i++;
				$j++;
				
				$jsonarray ["fields"] [$i] ["field"] ["id"] = $j;
				$jsonarray ["fields"] [$i] ["field"] ["title"] = "neg_review";
				$jsonarray ["fields"] [$i] ["field"] ["type"] = "textarea";
				$jsonarray ["fields"] [$i] ["field"] ["required"] = 0;
				$jsonarray ["fields"] [$i] ["field"] ["caption"] = "I Dislike";
				$jsonarray ["fields"] [$i] ["field"] ["value"] = "";
				$i++;
				$j++;
			}
			
			$jsonarray ["fields"] [$i] ["field"] ["id"] = $j;
			$jsonarray ["fields"] [$i] ["field"] ["title"] = "visitor";
			$jsonarray ["fields"] [$i] ["field"] ["type"] = "text";
			$jsonarray ["fields"] [$i] ["field"] ["required"] = 0;
			$jsonarray ["fields"] [$i] ["field"] ["caption"] = "Your Name";
			$jsonarray ["fields"] [$i] ["field"] ["value"] = "";
			$i++;
			$j++;
			
			$jsonarray ["fields"] [$i] ["field"] ["id"] = $j;
			$jsonarray ["fields"] [$i] ["field"] ["title"] = "vmail";
			$jsonarray ["fields"] [$i] ["field"] ["type"] = "text";
			$jsonarray ["fields"] [$i] ["field"] ["required"] = $mail_required;
			$jsonarray ["fields"] [$i] ["field"] ["caption"] = "Your Email";
			$jsonarray ["fields"] [$i] ["field"] ["value"] = "";
			$i++;
			$j++;
		}else{
			$Array['rating']=IJReq::getTaskData('rating');
			$Array['review']=IJReq::getTaskData('review');
			foreach($Array as $arrkey=>$arrvalue){
	    		foreach($arrvalue as $arke=>$arval){
	    			if(is_object($arval)){
						$arval1 = get_object_vars($arval);
						$Array[$arrkey] = $arval1;
	    			}else{
	    				$Array[$arrkey] = $arval;
	    			}
	    		}
    		}
    		$sectionID=$Array['review']['section'];
    		SPFactory::registry()->set( 'current_section',json_decode(json_encode($sectionID), FALSE));
    		if (file_exists(JPATH_SITE .'/components/com_sobipro/lib/models/review.php')) {
    			$tmpreviewobject = new tmprating();
	    		$tmpreviewobject->setSid($sid);
				defined( 'SOBI_ACL' ) || define( 'SOBI_ACL', 'front' );
	    		$savereview=$tmpreviewobject->saveReview($Array);
    		}
	    	if($savereview){
	    		IJReq::setResponseCode(200);
				IJReq::setResponseMessage("Thank you for your review");
	    	}else{
	    		IJReq::setResponseCode(500);
	    		IJException::setErrorInfo(__FILE__,__LINE__,__CLASS__,__METHOD__,__FUNCTION__);
	    		return false;
	    	}
		}
		return $jsonarray;
	}
	
	/*
	 * fetch criteria for the review vote
	 */
	private function reviewFields($sid){
		$query="SELECT * 
				FROM #__sobipro_sprr_fields 
				WHERE sid={$sid} 
				AND enabled=1";
		$this->db->setQuery ( $query );
		$fields = $this->db->loadAssocList ('fid');
		if( count( $fields ) ) {
			$ids = array_keys( $fields );
			$ids=implode(",",$ids);
			$query="SELECT sValue,explanation,language,id 
					FROM #__sobipro_language 
					WHERE id in ({$ids}) 
					AND oType='sprr_field' 
					AND section={$sid}";
			$this->db->setQuery ( $query );
			$labels = $this->db->loadAssocList();
			foreach ( $fields as $id => $field ) {
				foreach ( $labels as $label ) {
					if( $label[ 'id' ] == $id ) {
						if( !( isset( $field[ 'label' ] ) ) || $label[ 'language' ] == 'en-GB' ) {
							$field[ 'label' ] = $label[ 'sValue' ];
							$field[ 'explanation' ] = $label[ 'explanation' ];
						}
					}
					$field[ 'id' ] = $id;
					$fields[ $id ] = $field;
				}
				
				if( !( isset( $field[ 'label' ] ) ) ) {
					$field[ 'label' ] = 'Missing';
					$field[ 'explanation' ] = 'Missing';
				}
			}
		}else{
			$fields = array();
		}
		return $fields;
	}
	
	
	/**
     * @uses This function is used to add entry in selected categories of particular sectionid.
     * @example the json string will be like, : 
     *	{
	 *		"extName":"sobipro",
	 *		"extView":"isobipro",
 	 *		"extTask":"addentryField",
	 * 		"taskData":{
	 * 			"sectionID":"sectionID",
	 * 			"form":"form",(1/0)
	 * 			"fields":"fields"(array)
	 * 			}
	 * 	}
	 */
	function addentryField($tsid=0,$bypass=false){	
    	$sectionID=IJReq::getTaskData('sectionID',$tsid);
    	$form=IJReq::getTaskData('form');
    	SPFactory::registry()->set( 'current_section',json_decode(json_encode($sectionID), FALSE));
    	if($form==1){
	    	$query="SELECT so.id,so.name
					FROM #__sobipro_object as so 
					LEFT JOIN #__sobipro_category as sc on sc.id=so.id 
					WHERE so.oType='category' 
					AND so.parent={$sectionID}"; 
			$this->db->setQuery($query);
	       	$categories = $this->db->loadObjectList();
	    	$categoryArray['categoryFields']['caption']='categories';
	    	$categoryArray['categoryFields']['type']='multipleselect';
	    	$categoryArray['categoryFields']['name']='entry_parent';
	    	$categoryArray['categoryFields']['value']='';
	    	$categoryArray['categoryFields']['required']="1";
	    	$categoryArray['categoryFields']['options'][0]['name']='Select Categories';
	    	$categoryArray['categoryFields']['options'][0]['value']='';
	    	
	    	foreach($categories as $key=>$value){
				$categoryArray['categoryFields']['options'][$key]['name'] 		 = $value->name;
				$categoryArray['categoryFields']['options'][$key]['value'] 		 = $value->id;
			}
			
	    	$addentryArray['addentry']['categoryFields'][]=$categoryArray['categoryFields'];
	    	$query="SELECT sf.fid,sf.nid,sf.params,sf.fieldType,sf.required,sl.sValue  
					FROM #__sobipro_field as sf 
					LEFT JOIN #__sobipro_language as sl on sf.fid=sl.fid 
					WHERE sf.editable=1 
					AND sf.enabled=1
					AND sl.oType='field' 
					AND sl.sKey='name' 
					AND sf.section={$sectionID} 
					ORDER BY sf.position";
			$this->db->setQuery($query);
			$fields=$this->db->loadAssoclist();
			
			foreach($fields as $key=>$value){
				$raw = SPConfig::unserialize($value["params"]);
				foreach($raw as $r=>$p){
					$fields[$key][$r]=$p;
				}
			}
			
    		if(count($fields)==0){
        		IJReq::setResponseCode(204);
       		}
       		
			$count=0;
			$ccount=1;
	    	foreach($fields as $key=>$value){
	    		$addentryArray['addentry']['fields'][$count]['fid']=$value['fid'];
	    		$addentryArray['addentry']['fields'][$count]['required']=$value['required'];
				$addentryArray['addentry']['fields'][$count]['caption']=(isset($value['suffix']) && !empty($value['suffix'])) ? $value['suffix'].':' : $value['sValue'];
				if($value['fieldType']=='inbox'){
					$addentryArray['addentry']['fields'][$count]['type']="text";
				}else if($value['fieldType']=='chbxgroup'){
					$addentryArray['addentry']['fields'][$count]['type']="multipleselect";
				}else if($value['fieldType']=='geomap'){
					$addentryArray['addentry']['fields'][$count]['type']="map";
				}else if($value['fieldType']=='url'){
					$addentryArray['addentry']['fields'][$count]['type']="container";
				}else if($value['fieldType']=='calendar'){
					$addentryArray['addentry']['fields'][$count]['type']="datetime";
				}else{
					$addentryArray['addentry']['fields'][$count]['type']=$value['fieldType'];
				}
				$addentryArray['addentry']['fields'][$count]['value']="";
				$fid=$value['fid'];
				$addentryArray['addentry']['fields'][$count]['name']=$value['nid'];
				if($value['fieldType']=='url'){
					$addentryArray['addentry']['fields'][$count]['value'][0]['caption']=$value['labelsLabel'];
					$addentryArray['addentry']['fields'][$count]['value'][0]['type']="text";
					$addentryArray['addentry']['fields'][$count]['value'][0]['value']="";
					$addentryArray['addentry']['fields'][$count]['value'][0]['name']=$value['nid'];	
					$addentryArray['addentry']['fields'][$count]['value'][1]['caption']="websitevalue";
					$addentryArray['addentry']['fields'][$count]['value'][1]['type']="text";
					$addentryArray['addentry']['fields'][$count]['value'][1]['value']="";
					$addentryArray['addentry']['fields'][$count]['value'][1]['name']="field_website_url";	
					$addentryArray['addentry']['fields'][$count]['value'][2]['caption']="URL";
					$addentryArray['addentry']['fields'][$count]['value'][2]['type']="select";
					$addentryArray['addentry']['fields'][$count]['value'][2]['value']="";
					$addentryArray['addentry']['fields'][$count]['value'][2]['name']="field_website_protocol";	
					$allowedProtocols = $value['allowedProtocols'];
					$addentryArray['addentry']['fields'][$count]['value'][2]['options'][0]['name']='Select';
					$addentryArray['addentry']['fields'][$count]['value'][2]['options'][0]['value']='';
					foreach($allowedProtocols as $prkey=>$prval){
						$addentryArray['addentry']['fields'][$count]['value'][2]['options'][$prkey+1]['name']=$prval;
						$addentryArray['addentry']['fields'][$count]['value'][2]['options'][$prkey+1]['value']=$prval;
					}
				}
				
				if($value['fieldType']=='select'){
					$query="SELECT DISTINCT baseData 
							FROM #__sobipro_field_data 
							WHERE fid={$fid} 
							AND section={$sectionID} 
							AND enabled=1";
					$this->db->setQuery($query);
					$values=$this->db->loadResultArray();
					$addentryArray['addentry']['fields'][$count]['options'][0]['name']='Select '.$value['sValue'].'...';
					$addentryArray['addentry']['fields'][$count]['options'][0]['value']='';
					
					if($values[0]){
						foreach($values as $kse=>$ve){
							$query="SELECT sValue   
									FROM #__sobipro_language 
									WHERE `sKey` LIKE '{$ve}'";
							$this->db->setQuery($query);
							$result=$this->db->loadResult();
							$addentryArray['addentry']['fields'][$count]['options'][$kse+1]['name']=($result) ? trim($result) : trim($ve);
							$addentryArray['addentry']['fields'][$count]['options'][$kse+1]['value']=trim($ve);
						}
					}else{
						$query="SELECT optValue 
								FROM #__sobipro_field_option 
								WHERE fid={$fid} 
								AND optParent=''  
								ORDER BY optPos";
						$this->db->setQuery($query);
						$groups=$this->db->loadResultArray();
						foreach($groups as $ke=>$ve){
							$query="SELECT optValue 
									FROM #__sobipro_field_option 
									WHERE fid={$fid} 
									AND optParent='{$ve}'  
									ORDER BY optPos";
							$this->db->setQuery($query);
							$values=$this->db->loadResultArray();
							
							if($values){
								foreach($values as $kad=>$vad){
									$query="SELECT sValue   
											FROM #__sobipro_language 
											WHERE `sKey` LIKE '{$vad}'";
									$this->db->setQuery($query);
									$result=$this->db->loadResult();
									$addentryArray["addentry"]["fields"][$count]["options"][$ccount]["name"]=($result) ? trim($ve).'-'.$result : trim($ve).'-'.$vad;
									$addentryArray["addentry"]["fields"][$count]["options"][$ccount]["value"]=trim($vad);
									$ccount++;
								}
							}else{
								$query="SELECT sValue   
										FROM #__sobipro_language 
										WHERE `sKey` LIKE '{$ve}'";
								$this->db->setQuery($query);
								$result=$this->db->loadResult();
								$addentryArray['addentry']['fields'][$count]['options'][$ke+1]['name'] = ($result) ? trim($result) : trim($ve);
								$addentryArray['addentry']['fields'][$count]['options'][$ke+1]['value']=trim($ve);
							}
							$addentryArray['addentry']['fields'][$count]['options']=array_values($addentryArray['addentry']['fields'][$count]['options']);
						}
					}
				}
				
				if($value["fieldType"]=='chbxgroup'){
					$query="SELECT optValue 
							FROM #__sobipro_field_option 
							WHERE fid={$fid} 
							ORDER BY optPos";
					$this->db->setQuery($query);
					$values=$this->db->loadResultArray();
					
					$addentryArray['addentry']['fields'][$count]['options'][0]['name']='Select '.$value['sValue'].'...';
					$addentryArray['addentry']['fields'][$count]['options'][0]['value']='';
					
					foreach($values as $ke=>$ve){
						$query="SELECT sValue   
								FROM #__sobipro_language 
								WHERE `sKey` LIKE '{$ve}'";
						$this->db->setQuery($query);
						$result=$this->db->loadResult();
						$addentryArray['addentry']['fields'][$count]['options'][$ke+1]['name']=($result) ? trim($result) : trim($ve);
						$addentryArray['addentry']['fields'][$count]['options'][$ke+1]['value']=trim($ve);
					}
				}
				
				if($value["fieldType"]=='multiselect'){
					$query="SELECT optValue 
							FROM #__sobipro_field_option 
							WHERE fid={$fid} 
							AND optParent=''  
							ORDER BY optPos";
					$this->db->setQuery($query);
					$groups=$this->db->loadResultArray();
					$i=1;
					$addentryArray["addentry"]["fields"][$count]["options"][0]["name"]='Select '.$value["sValue"].'...';
					$addentryArray["addentry"]["fields"][$count]["options"][0]["value"]='';
					
					foreach($groups as $ke=>$ve){
						$query="SELECT optValue 
								FROM #__sobipro_field_option 
								WHERE fid={$fid} 
								AND optParent like '{$ve}'  
								ORDER BY optPos";
						$this->db->setQuery($query);
						$values=$this->db->loadResultArray();
						if($values){
							foreach($values as $k=>$v){
								$query="SELECT sValue   
										FROM #__sobipro_language 
										WHERE `sKey` LIKE '{$v}'";
								$this->db->setQuery($query);
								$result=$this->db->loadResult();
								$addentryArray['addentry']['fields'][$count]['options'][$i]['name']=($result) ? trim($ve).'-'.$result : trim($ve).'-'.$v;
								$addentryArray['addentry']['fields'][$count]['options'][$i]['value']=trim($v);
								$i++;
							}
						}else{
							$query="SELECT sValue   
									FROM #__sobipro_language 
									WHERE `sKey` LIKE '{$ve}'
									AND fid={$fid}";
							$this->db->setQuery($query);
							$result=$this->db->loadResult();
							$addentryArray['addentry']['fields'][$count]['options'][$ke+2]['name']=($result) ? trim($result) : trim($v);
							$addentryArray['addentry']['fields'][$count]['options'][$ke+2]['value']=trim($ve);
						}
					}
				}
				$count++;
			}
			$jsonarray = array();
	    	$jsonarray['code']		 = 200;
	    	$jsonarray['search']     = $addentryArray['addentry'];
			return $jsonarray;
	    }else{
			require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "models" . DS . "entry.php");
			require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "ctrl" . DS . "interface.php");
			require_once (JPATH_SITE . DS . "components" . DS . "com_sobipro" . DS . "lib" . DS . "ctrl" . DS . "entry.php");
			defined( 'SOBI_ACL' ) || define( 'SOBI_ACL', 'front' );
			$SPJoomlaMainFrame = new SPJoomlaMainFrame();
			$token = $SPJoomlaMainFrame->token();
			JRequest::setVar( $token, 1 );
			$query="SELECT sc.sValue 
					FROM #__sobipro_config as sc 
					WHERE sc.cSection='entry'
					AND sc.sKey='name_field' 
					AND sc.cSection='entry'
					AND sc.section={$sectionID}";
			$this->db->setQuery($query);
			$name_field = $this->db->loadResult();
			JRequest::setVar( 'task', 'entry.save' );
			$fields=IJReq::getTaskData('fields');
			foreach($fields as $fkey=>$fvalue){
	    		foreach($fvalue as $fke=>$fval){
	    			if(is_object($fval)){
						$fval1 = get_object_vars($fval);
						$fexobj=explode(',',$fval1);
						$_POST[$fke] = (count($fexobj)==1) ? implode(' ',$fexobj) : $fexobj;
	    			}else{
	    				$fexobj1=explode(',',$fval);
						$_POST[$fke] = (count($fexobj1)==1) ? implode(' ',$fexobj1) : $fexobj1;
	    			}
	    		}
    		}
    		
   			$section = $_POST['pid'];
   			$cfginstanse = SPConfig::getInstance();
   			$cfginstanse->addIniFile( 'etc.config', true );
       		$cfginstanse->addTable( 'spdb_config', $section );
   			SPFactory::registry()->set( 'current_section',json_decode(json_encode($section), FALSE));
   			
		    $query="SELECT sf.fid,sf.nid,sf.params,sf.fieldType,sl.sValue  
					FROM #__sobipro_field as sf 
					LEFT JOIN #__sobipro_language as sl on sf.fid=sl.fid 
					WHERE sf.inSearch=1 
					AND sf.enabled=1
					AND sl.oType='field' 
					AND sl.sKey='name' 
					AND sf.section={$section} 
					ORDER BY sf.position";
			$this->db->setQuery($query);
			$configfields=$this->db->loadAssoclist();
			foreach($configfields as $key=>$value){
				$raw = SPConfig::unserialize($value["params"]);
				if(!array_key_exists("searchMethod", $raw) or $raw["searchMethod"]=="general"){
					unset($configfields[$key]);
				}else{
					unset($configfields[$key]["params"]);
					foreach($raw as $r=>$p){
						$configfields[$key][$r]=$p;
					}
				}
				/*
				 * fetch the suffix
				 */
				$query="SELECT sl.sKey,sl.sValue  
						FROM #__sobipro_language as sl 
						WHERE sl.fid={$value['fid']}";
				$this->db->setQuery($query);
				$lang=$this->db->loadObject();
				if($lang){
					preg_match('|\[|',$lang->sValue,$matches);
					if(isset($matches[0]) and $matches[0]){
						$srch=array('[',']');
						$lang->sValue=str_replace($srch,'',$lang->sValue);
						$tmp=explode(':',$lang->sValue);
						$tmp=explode('.',$tmp[1]);
						
						$query="SELECT sValue  
								FROM #__sobipro_config  
								WHERE cSection='{$tmp[0]}' 
								AND sKey='{$tmp[1]}'";
						$this->db->setQuery($query);
						$result=$this->db->loadObject();
						$configfields[$key]['suffix']=$result->sValue;
					}
				}
			}
    		
			foreach($configfields as $configke=>$configval){
				if(in_array($configval['nid'],array_keys($_POST))){
					$inputForm[$configval['nid']]=$configval['inputForm'];
				}
			}
			
			if($inputForm['field_deal_start']=='dd.mm.yy' || $inputForm['field_deal_start']=='d.m.yy'){
				$start_selector=date("d.m.Y H:i", strtotime($_POST['field_deal_start']));
			}else if($inputForm['field_deal_start']=='dd-mm-yy'){
				$start_selector=date("d-m-Y H:i", strtotime($_POST['field_deal_start']));
			}else if($inputForm['field_deal_start']=='mm/dd/yy'){
				$start_selector=date("m/d/Y H:i", strtotime($_POST['field_deal_start']));
			}else if($inputForm['field_deal_start']=='yy-mm-dd'){
				$start_selector=date("Y-m-d H:i", strtotime($_POST['field_deal_start']));
			}else if($inputForm['field_deal_start']=='yy.mm.dd'){
				$start_selector=date("Y.m.d H:i", strtotime($_POST['field_deal_start']));
			}
			
			$_POST['field_deal_start_selector'] = $start_selector;
			$_POST['field_deal_start'] = gmmktime(date('H',strtotime($start_selector)), date('i',strtotime($start_selector)), null, date('m',strtotime($start_selector)), date('d',strtotime($start_selector)), date('Y',strtotime($start_selector))  );
			$_POST['field_deal_start'] = $_POST['field_deal_start']*1000;
			
			if($inputForm['field_deal_end']=='dd.mm.yy' || $inputForm['field_deal_end']=='d.m.yy'){
				$end_selector=date("d.m.Y H:i", strtotime($_POST['field_deal_end']));
			}else if($inputForm['field_deal_end']=='dd-mm-yy'){
				$end_selector=date("d-m-Y H:i", strtotime($_POST['field_deal_end']));
			}else if($inputForm['field_deal_end']=='mm/dd/yy'){
				$end_selector=date("m/d/Y H:i", strtotime($_POST['field_deal_end']));
			}else if($inputForm['field_deal_end']=='yy-mm-dd'){
				$end_selector=date("Y-m-d H:i", strtotime($_POST['field_deal_end']));
			}else if($inputForm['field_deal_end']=='yy.mm.dd'){
				$end_selector=date("Y.m.d H:i", strtotime($_POST['field_deal_end']));
			}
			
			$_POST['field_deal_end_selector'] = $end_selector;
			$_POST['field_deal_end'] = gmmktime(date('H',strtotime($end_selector)), date('i',strtotime($end_selector)), null, date('m',strtotime($end_selector)), date('d',strtotime($end_selector)), date('Y',strtotime($end_selector))  );
			$_POST['field_deal_end']=$_POST['field_deal_end']*1000;
			$pid=$_POST['pid'];
			$entry_parent=$_POST['entry_parent'];
			$parentEntry=(is_array($entry_parent)) ? $entry_parent[0] : $entry_parent;
			
			$query="SELECT so.name
					FROM #__sobipro_object as so 
					WHERE so.oType='section' 
					AND so.id={$pid}";
			$this->db->setQuery($query);
       		$SectionName = $this->db->loadResult();
       		if(is_array($entry_parent)){
	       		foreach($entry_parent as $ent){
		       		$query="SELECT so.name
							FROM #__sobipro_object as so 
							WHERE so.oType='category' 
							AND so.id={$ent}";
					$this->db->setQuery($query);
		       		$Categories = $this->db->loadResultArray();
		       		foreach($Categories as $sec){
		       			$sections[]=$sec;
		       		}
	       		}
       		}else{
       			$query="SELECT so.name
						FROM #__sobipro_object as so 
						WHERE so.oType='category' 
						AND so.id={$entry_parent}";
				$this->db->setQuery($query);
		       	$sections = $this->db->loadResultArray();
       		}
       		$pattern="";
       		foreach($sections as $secVal){
       			$pattern.=" ".$SectionName." ".">"." ".$secVal." ";
       		}
       		
       		JRequest::setVar( 'parent_path', $pattern );
       		if(is_array($entry_parent)){
       			JRequest::setVar( 'entry_parent', implode(',',$entry_parent) );
       		}else{
       			JRequest::setVar( 'entry_parent', $entry_parent );
       		}
       		JRequest::setVar( $_FILES, $_FILES );
			$model = 'SPEntry';
			$this->_model = new $model();
			$this->_model->set('parent',$parentEntry);
			$this->_model->getRequest( 'entry', 'post' );
			$ctroller='SPEntryCtrl';
			$fun='setModel';
			Sobi::Trigger( $ctroller, $fun , array( &$model ) );
			$SPEntryCtrl = new SPEntryCtrl();
			$modelsave= $this->_model->save($request = 'post');
			IJReq::setResponse(200,"Thank you for your add entry");
		}
    }
	
	function subcategory($id){
		$query="SELECT so.id,so.name,so.counter,sc.description,sc.icon,sc.showIcon 
				FROM #__sobipro_object as so 
				LEFT JOIN #__sobipro_category as sc on sc.id=so.id 
				WHERE so.oType='category' AND so.parent={$id}";
		$this->db->setQuery($query);
        $subrows = $this->db->loadObjectList();
        $jsonarray=array();
		foreach($subrows as $ke=>$val){
			$jsonarray[$ke]['id'] = $val->id;
			$jsonarray[$ke]['title'] = $val->name;
			$subcount1 = $this->getcount($val->id);
			if($subcount1 > 0){
				$jsonarray[$ke]['subcategory']    = $this->subcategory($val->id);
			}
		}
		return $jsonarray;
	}
	
	function getcount($id){
		$query="SELECT count(so.id) 
				FROM #__sobipro_object as so 
				LEFT JOIN #__sobipro_category as sc on sc.id=so.id 
				WHERE so.oType='category' AND so.parent={$id}";
		$this->db->setQuery($query);
	    $count = $this->db->loadResult();
		return $count;
	}	
}