<?php
/**
 * Ews
 *
 * Copyright 2012- by Andreas Bilz <anti@herooutoftime.com>
 *
 * Ews is free software; you can redistribute it and/or modify it under the
 * terms of the GNU General Public License as published by the Free Software
 * Foundation; either version 2 of the License, or (at your option) any later
 * version.
 *
 * Ews is distributed in the hope that it will be useful, but WITHOUT ANY
 * WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR
 * A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with
 * Ews; if not, write to the Free Software Foundation, Inc., 59 Temple
 * Place, Suite 330, Boston, MA 02111-1307 USA
 *
 * @package ews
 */
/**
 * The base class for Ews
 *
 * @package ews
 * @todo Is there any need to send out reminders? If yes, do it by cron...
 */

require_once(MODX_CORE_PATH . 'components/ews/includes/ews-calendar/ews.autoloader.php');

class Ews {
    public $modx;
    public $config = array();
	public $message;

    function __construct(modX &$modx,array $config = array()) {
    	$this->modx =& $modx;
        
        $corePath = $modx->getOption('ews.core_path',null,$modx->getOption('core_path').'components/ews/');
        $assetsUrl = $modx->getOption('ews.assets_url',null,$modx->getOption('assets_url').'components/ews/');

        $this->config = array_merge(array(
            'corePath'      => $corePath,
            'chunksPath'    => $corePath.'elements/chunks/',
            'snippetsPath'  => $corePath.'elements/snippets/',
            'includesPath'  => $corePath.'includes/'
        ),$config);
		
        $this->modx->lexicon->load('ews:default');
		$this->modx->lexicon->load('ews:error');
		$this->modx->lexicon->load('ews:frontend');
        
        $s = $modx->getOption('ews.server');
        $u = $modx->getOption('ews.user');
        $p = $modx->getOption('ews.password');
        
        try {
            $this->ews = new ExchangeWebServices($s, $u, $p);
        } catch (Exception $e) {
            echo 'Exception abgefangen: ',  $e->getMessage(), "\n";
        }        
            
    }
    
    /**
     * getCalendarList
     * Retrieve calendar items from Exchange
     * @param integer $m The month to search for
     * @param integer $y The year to search for
     * @param mixed $range How many months to look forward
     * @todo Make highly flexible for search options
     */
    public function getCalendarList($m, $y, $range = false, $limit = false) {
		
		$start = "$m/01/$y -00";
        $last = date('t', strtotime($start));
		$end = "$m/$last/$y -00";
        
        if($limit) {
            $d = date('d');
            $em = $m + 2;
            $ey = $m > 11 ? $y+1 : $y;
            $start = "$m/$d/$y -00";
            $end = "$em/$d/$ey -00";
        }
		
        $request = new EWSType_FindItemType();
        $request->Traversal = EWSType_ItemQueryTraversalType::SHALLOW;
        
        $request->ItemShape = new EWSType_ItemResponseShapeType();
        $request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;
        
        $request->CalendarView = new EWSType_CalendarViewType();
        $request->CalendarView->StartDate = date('c', strtotime($start));
        if($range) {
            $request->CalendarView->EndDate = date('c', strtotime($end) + $range * date('t', strtotime($start)) * 24 * 60 * 60);
        } else {
            $request->CalendarView->EndDate = date('c', strtotime($end));
        }
		
        $request->ParentFolderIds = new EWSType_NonEmptyArrayOfBaseFolderIdsType();
        $request->ParentFolderIds->DistinguishedFolderId = new EWSType_DistinguishedFolderIdType();
        $request->ParentFolderIds->DistinguishedFolderId->Id = EWSType_DistinguishedFolderIdNameType::CALENDAR;
		$response = $this->ews->FindItem($request);
        
        $items = $response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->CalendarItem;
        var_dump($response);
		if($response)
			return $response;
		return false;
    }
    
	/**
	 * setError
	 * Sets error messages via MODX lexicon
	 *
	 * @param string $error A lexicon entry (string after last '.')
	 */
	public function setError($error) {
		$this->message[] = array();
		$this->message[]['text'] = $this->modx->lexicon('ews.error.' . $error);
		
		// Take the red pill or the green? Check for success messages
		if($error == 'success')
			$this->message[]['type'] = true;
	}
	
	/**
	 * makeItem
	 * Create/Update a calendar item
	 *
	 * @param string $tpl Chunk to render the input form
	 * @param array $data $_POST, $_FILES & scriptProperties already sanitized
	 * @todo Increase validation level on each field
	 * @todo Hook for update process (input type hidden value set)
	 * @todo Check how to make update process work
	 * @return mixed error/success message with form output
	 */
	public function makeItem($tpl, $data) {
		
        $post = $data['post'];
        $files = $data['files'];
		$props = $data['props'];
		$get = $data['get'];
		
		//https://mail.bundesliga.at/owa/?ae=PreFormAction&a=Open&t=IPM.Appointment&id=RgAAAADztGej1vchS5deumnC6FTQBwAWMjwbjh5WS7zXYTuZYYeKAAAA%2fVueAAAWMjwbjh5WS7zXYTuZYYeKAAABSR24AAAP&clr=-1&pspid=_1338835523518_828275302
		
		// Get an item by its uid for editing...
		//if(isset($get) && !empty($get['uid']))
		//	$item = $this->getItem($get['uid']);
		
		//if($item && !isset($post['edit']))
		//	return $this->getForm($tpl, null, $item);
		
		//if($post['edit'] && $post['uid'] && $post['change_key'])
		//	$this->editItem($post['uid'], $post['change_key']);
		
        if(!$post)
			return $this->getForm($tpl);
			
		if(!is_string($post['subject']) || empty($post['subject']))
			$this->setError('nosubject');
		
		if(empty($post['start']['date']) || empty($post['end']['date']))
			$this->setError('nodate');
		
		// Not validated dates yet
		$start = strtotime($post['start']['date'] . ' ' . $post['start']['hour'] . ':' . $post['start']['minute']);
		$end = strtotime($post['end']['date'] . ' ' . $post['end']['hour'] . ':' . $post['end']['minute']);
		
		// Validate dates
		if(!checkdate(date('n', $start) , date('j', $start) , date('Y', $start)))
			$this->setError('nodate');
		if(!checkdate(date('n', $end) , date('j', $end) , date('Y', $end)))
			$this->setError('nodate');
		
		if(is_array($this->message))
			return $this->getForm($tpl, $this->message);
			
		// Successful validation
		$start = date('Y-m-d\TH:i:00', $start);
		$end = date('Y-m-d\TH:i:00', $end);
        
		if(isset($post['allday']))
			$allday = true;
        
		// Start the creation process
		$request = new EWSType_CreateItemType();
		$request->Items = new EWSType_NonEmptyArrayOfAllItemsType();
		$request->Items->CalendarItem = new EWSType_CalendarItemType();
		
		// Set the item properties
		$request->Items->CalendarItem->Subject = $post['subject'];
		$request->Items->CalendarItem->Start = $start;
		$request->Items->CalendarItem->End = $end;
		$request->Items->CalendarItem->Importance = $post['importance'];
        $request->Items->CalendarItem->Location = $post['location'];
		$request->Items->CalendarItem->IsAllDayEvent = $allday;
		//$request->Items->CalendarItem->RequiredAttendees->Attendee->Mailbox->EmailAddress = 'anti@herooutoftime.com';
		
		$request->Items->CalendarItem->Body = new EWSType_BodyType();
		$request->Items->CalendarItem->Body->BodyType = 'HTML';
		$request->Items->CalendarItem->Body->_ = $post['body'];
		
		$request->SendMeetingInvitations = EWSType_CalendarItemCreateOrDeleteOperationType::SEND_TO_ALL_AND_SAVE_COPY;
		$request->SendMeetingInvitationsSpecified = true;
		
		// Add attendee(s)
		if(isset($post['email']))
			$email = $this->sendMail($request, $post['email']);
			
		$response = $this->ews->CreateItem($request);
		$item = $response->ResponseMessages->CreateItemResponseMessage->Items;
		
        // Add the attachments
        if($files && is_array($files))
            $attachments = $this->addAttachments($item, $files);
        
		if($attachments)
			$this->setError('attachments_added');
			
		// Get the response from the Exchange Server
		// This might help on some issues when handling data
		//if($props['debug'])
			echo '<pre>'.print_r($response, true).'</pre>';
		
		// Set the success message
		$this->setError('success');
		
		return $this->getForm($tpl, $this->message);
	}
	
	/**
	 * editItem
	 * Edit a single calendar item
	 *
	 * @param string $uid A unique identifier of an event
	 * @param string $change_key A unique key to change an event
	 */
	public function editItem($uid, $change_key) {
		$request = new EWSType_UpdateItemType();
		$request->SendMeetingInvitationsOrCancellations = 'SendToNone';
		$request->MessageDisposition = 'SaveOnly';
		$request->ConflictResolution = 'AlwaysOverwrite';
		
		$request->ItemChanges = array();

		// Build out item change request.
		$change = new EWSType_ItemChangeType();
		$change->ItemId = new EWSType_ItemIdType();
		$change->ItemId->Id = $uid;
		$change->ItemId->ChangeKey = $change_key;
		
		$change->Updates = new EWSType_NonEmptyArrayOfItemChangeDescriptionsType();
		$change->Updates->SetItemField = array(); // Array of fields to be update
		$change->Updates->DeleteItemField = array(); // Array of fields to be removed
		
		// Update Firstname (simple property)
		$field = new EWSType_SetItemFieldType();
		$field->FieldURI->FieldURI = 'calendars:IsCancelled';
		$field->Calendar = new EWSType_CalendarItemType();
		$field->Calendar->IsCancelled = true;
		
		$change->Updates->SetItemField[] = $field;
		// Set all changes
		$request->ItemChanges[] = $change;
		
		// Send request
		$response = $this->ews->UpdateItem($request);
		echo '<pre>'.print_r($response, true).'</pre>';
		
	}
	
	public function getItem($uid) {
		// Form the GetItem request
		$request = new EWSType_GetItemType();
		
		// Define which item properties are returned in the response
		$itemProperties = new EWSType_ItemResponseShapeType();
		$itemProperties->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;
		
		// Add properties shape to request
		$request->ItemShape = $itemProperties;
		
		// Set the itemID of the desired item to retrieve
		$id = new EWSType_ItemIdType();
		$id->Id = $uid;
		
		$request->ItemIds->ItemId = $id;
		
		//  Send the listing (find) request and get the response
		$response = $this->ews->GetItem($request);	
		if(!$response)
			return false;
		$item = $response->ResponseMessages->GetItemResponseMessage->Items->CalendarItem;
		return $item;
	}
	
	/**
     * getForm
     * Generate the search form
     *
     * @param string $tpl The form chunk to output
     *
     * @todo Generate error message for each given field
     * @todo Define update process
     */
    public function getForm($tpl, $errors = null, $item = null) {
		
		// Start from scratch and set current time values
		$now = time();
		$current_hour = date('G', $now);
		$current_minute = date('i', $now);
		
		//$start_hour = date('G', $now);
		//$start_minute = date('i', $now);
		//$end_hour = date('G', $now);
		//$end_minute = date('i', $now);
		
		//Check if we need to handle an edit/update process
		//$item = $this->modx->toArray($item);
		if($item) {
			$data = array(
				'subject'		=> $item->Subject,
				'location'		=> $item->Location,
				'start_date'	=> $item->Start,
				'end_date'		=> $item->End,
				'importance'	=> $item->Importance,
				'submit'		=> 'Editieren',
				'editable'		=> $item->ItemId->Id,
				'change_key'	=> $item->ItemId->ChangeKey,
				'cancelable'	=> 1
			);
			
			$start_hour = date('G', strtotime($item->Start));
			$start_minute = date('i', strtotime($item->Start));
			$end_hour = date('G', strtotime($item->End));
			$end_minute = date('i', strtotime($item->End));
			
		}
		
		/**
		 * @todo getChunk - fails for multiple iterations, known issue...
		 */
		// Generate the hours dropdown with current hour preselected
		for($i = 1;$i < 25; $i++) {
			$selected = $current_hour == $i ? 'selected' : '';
			$hours .= $this->modx->getChunk('option', array('item' => $i, 'selected' => $selected));
		}
		
		// Generate the minutes dropdown with current minute preselected
		for($i = 0; $i < 60; $i++) {
			$selected = $current_minute <= $i && $current_minute >= $i-5 ? 'selected' : '';
			$minutes .= $this->modx->getChunk('option', array('item' => $i, 'selected' => $selected));
			$i = $i+4;
		}
		
		$type = false;
		foreach($errors as $error) {
			if($error['type'] === true)
				$type = true;
			$message .= $error['text'] . '<br/>';
		}
		return $this->modx->getChunk($tpl, array('hours' => $hours, 'minutes' => $minutes, 'errors' => $message, 'type' => $type));
		//return $this->modx->getChunk($tpl, array_merge($data, array('hours' => $hours, 'minutes' => $minutes, 'errors' => $message, 'type' => $type)));
    }
	
	/**
	 * sendMail
	 * Sends mail if set to attendees
	 * @todo Allow multiple emails, separated by comma (',')
	 * @todo Validate email addresses
	 * @param mixed Comma-separated string or array of email addresses
	 *
	 * <t:Attendee>
     *  <t:Mailbox>
     *   <t:EmailAddress>attendee0@example.com</t:EmailAddress>
     *  </t:Mailbox>
     * </t:Attendee>
	 */
	public function sendMail($request, $emails = null) {
		
		// Validate email address
		// PHP >= v5.2.0 uses filter_var
		if(filter_var($emails, FILTER_VALIDATE_EMAIL))
			$request->Items->CalendarItem->RequiredAttendees->Attendee->Mailbox->EmailAddress = $emails;
		
		//$attRequest = new EWSType_AttendeeType();
		//$attRequest->ParentItemId = $item->CalendarItem->ItemId;
		//$attRequest->RequiredAttendees->Attendee->Mailbox->EmailAddress = $emails;
		//$attendee[0] = new EWSType_AttendeeType();
		
		$this->setError('emails_sent');
        return true;
	
	}
	
    /**
     * addAttachments
     * Adds attachments to a calendar item
     *
     * @param object $item The current calendar item
     * @param array $files Files to add to the calendar item
     *
     */ 
    public function addAttachments($item, $files = null) {
        // Create attachment(s)
        if(!$files) 
            return false;
        
        $attachments = array();
        $i = 0;
        foreach($files as $file) {
            $attachments[$i] = new EWSType_FileAttachmentType();
            $attachments[$i]->Content = file_get_contents($file['tmp_name']);
            $attachments[$i]->Name = $file['name'];
            $attachments[$i]->ContentType = $file['type'];
            $i++;
        }
		
		// Attach files to message
		$attRequest = new EWSType_CreateAttachmentType();
		$attRequest->ParentItemId = $item->CalendarItem->ItemId;
		$attRequest->Attachments->FileAttachment = $attachments;
		
		$attResponse = $this->ews->CreateAttachment($attRequest);
		$attResponseId = $attResponse->ResponseMessages->CreateAttachmentResponseMessage->Attachments->FileAttachment->AttachmentId;
		
		// Save message id from create attachment response
		$msgItemId = new EWSType_ItemIdType();
		$msgItemId->ChangeKey = $attResponseId->RootItemChangeKey;
		$msgItemId->Id = $attResponseId->RootItemId;
        return true;
    }
    
	public function getCalendarItems() {
		
	}
	
	public function getItemDetail($id) {
		$request = new EWSType_GetItemType();
		
		$request->ItemShape = new EWSType_ItemResponseShapeType();
		$request->ItemShape->BaseShape = EWSType_DefaultShapeNamesType::ALL_PROPERTIES;
		
		$request->ItemIds = new EWSType_NonEmptyArrayOfBaseItemIdsType();
		$request->ItemIds->ItemId = new EWSType_ItemIdType();
		$request->ItemIds->ItemId->Id = $id;
		$detail = $this->ews->GetItem($request);
		$body = $detail->ResponseMessages->GetItemResponseMessage->Items->CalendarItem->Body;
		return $body;
	}
	
    /**
     * getCalendarView
     * Generate the view for the retrieved items by days
     * @todo Make different views available
     * @todo Make flexible via chunks
     */
    public function getCalendarView($props) {
        
		// Check for given parameters
		$year  = $props['year'];
		$month = $props['month'];
        $range = $props['range'];
		$limit = $props['limit'];
        $navTpl= $props['navTpl'];
        
		// Fetch data via EWS
        $response = $this->getCalendarList($month, $year, $range, $limit);
		$items = $response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->CalendarItem;
        if(!is_array($items)) {
            $items = array();
            $items[] = $response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->CalendarItem;
        }
        
        $range = date('t', strtotime("$month/01/$year"));  //Monthly range
        //$first = '01';              //Start months at 01
        //if($limit) {
        //    $range = $limit;        //List range
        //    $first = date('d');     //Start list with today
        //}
        
        $calendar = array();
        
        // Build a monthly view (01 - 31)
        if(!$limit) {
            for($i = 0; $i <= ($range - 1); $i++) {
                $calendar[date('m/d/Y', strtotime("$year-$month-01") + $i*24*60*60)] = '';
            }
        }
        
        // Store the events
        $j = 1;
        foreach($items as $item) {
            
            if($limit && $j > $limit) break;
            
            $calendar[date('m/d/Y', strtotime($item->Start))][] = $item;
            $j++;
        }
        
        $day = key($calendar);
        
        // Do we need a navigation?
        if($navTpl) {
            
            // Prepare data for month navigation
            $lastMonth = $month - 1;
            $lastYear  = $year;
            $nextMonth = $month + 1;
            $nextYear  = $year;
            
            if($nextMonth == 13) {
                $nextMonth = 1;
                $nextYear++;
            }
            if($lastMonth == 0) {
                $lastMonth = 12;
                $lastYear--;
            }
            
            $currentMonth = strftime('%B %Y', strtotime("$year-$month-01"));
            
            // Generate month navigation
            $output .= $this->modx->getChunk($navTpl, array(
                                                                         'lastYear' => $lastYear,
                                                                         'lastMonth' => $lastMonth,
                                                                         'currentMonth' => $currentMonth,
                                                                         'nextYear' => $nextYear,
                                                                         'nextMonth' => $nextMonth
                                                                         )
                                             );
        }
		
		$output .= '<div class="clearfix">';
        
        // Render header (weekdays) and push empty day elements
        // only if limit not set
        if(!$limit) {
            
            // Generate header, only if no limit is set
            $output .= $this->modx->getChunk($props['headerTpl']);
            
            // Generate space elements for getting weekday of month 1st day
            for($i = date('N', strtotime($day)); $i > 1; $i--) {
            	$events .= $this->modx->getChunk($props['dayTpl'], array('day' => '', 'dayClass' => $props['dayClass']));
            }
        }
        
		// Generate output - DAY
        $k = 1;
        foreach($calendar as $key => $val) {
            $elements = '';
            if(is_array($val))
                $elements = $this->createItem($props, (array) $val);
			$daynum = date('d', strtotime($key));
            $class = $props['dayClass'] . (date('N', strtotime($key)) == 7 ? ' is_sunday' : '');
            
			$args = array(
				'day'		=> $key,
				'daynum'	=> $daynum,
				'items'		=> $elements,
                'attributes'=> $props['dayAttr'],
                'dayClass'  => $class
			);
			
            //DayTpl
			$events .= $this->modx->getChunk($props['dayTpl'], $args);
            $k++;
        }
		$output .= $this->modx->getChunk($props['outerTpl'], array('items' => $events, 'attributes' => $props['outerAttr']));
		$output .= '</div>';
        echo $output;
        return;
    }
    
    /**
     * createItem
     * Generate a single event
     */
    public function createItem($props, $items) {
		
        $output = '';
        foreach($items as $item) {
            
            $body = $this->getItemDetail($item->ItemId->Id);
            $arg = array(
				'uid'			=> $item->ItemId->Id,
                'startTime'     => $item->IsAllDayEvent ? 'ganztags' : date('H:i', strtotime($item->Start)),
                'endTime'       => $item->IsAllDayEvent ? false : date('H:i', strtotime($item->End)),
                'start'         => $item->Start,
                'end'           => $item->End,
                'location'      => $item->Location,
                'subject'       => $item->Subject,
                'detail'        => $body->_,
                'fullday'       => $item->IsAllDayEvent,
                'eventAttr'     => $props['eventAttr']
                         );
            //ItemTpl
            $output .= $this->modx->getChunk($props['eventTpl'], $arg);
        }
        return $output;
    }
    
    /**
     * generateICS0
     * Generate webcal/export for ICS
     * @param array $props Properties
     */
    public function generateICS($props) {
        $cal = new vcalendar();
		$cal->setProperty( "x-wr-calname", $props['calname'] );
        $cal->setProperty( "X-WR-CALDESC", $props['caldesc'] );
        $cal->setProperty( "X-WR-TIMEZONE", $props['timezone'] );
		
		$response = $this->getCalendarList($props['month'], $props['year'], $props['range']);
		if(is_object($response))
			$items = $response->ResponseMessages->FindItemResponseMessage->RootFolder->Items->CalendarItem;
		
		foreach($items as $item) {
			$this->addIcsItem($cal, $item);
		}
		return $cal->createCalendar();
		//return $cal->returnCalendar();
    }
	
	/**
	 * addIcsItem
	 * Adds an event item to the referenced calendar element
	 */
	public function addIcsItem(&$cal, $item) {
		$body = $this->getItemDetail($item->ItemId->Id);
		$event = &$cal->newComponent('vevent');
		$event->setProperty('dtstart', array('timestamp' => strtotime($item->Start)));
		$event->setProperty('dtend', array('timestamp' => strtotime($item->End)));
		$event->setProperty('summary', $item->Subject);
		$event->setProperty('description', preg_replace("/[\n\r]/","", strip_tags($body->_)));
	}
}
