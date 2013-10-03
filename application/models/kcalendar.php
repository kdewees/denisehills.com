<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Kcalendar extends CI_Model {

	var $month = 99;
	var $year = 99;
	var $today = '';
	var $month_end_day = '';
	var $unix_date = '';
	var $unix_month_end_day = '';
	var $unix_month_beg_day = '';
	var $mysql_date = '';
	var $mysql_month_beg_day = '';
	var $mysql_month_end_day = '';
	var $data = array();
	var $calendar_prefs = array('show_next_prev' => TRUE,
							'next_prev_url' => '',
							'month_type' => 'long', 
							'start_day' => 'sunday',
							'day_type' => 'long',
							'template' => '');

/* ======================================================== construct */
    function __construct()
    {
        // Call the Model constructor
        parent::__construct();
        $this->load->helper('date');

		$this->calendar_prefs['next_prev_url'] = site_url('school_calendar');
    } //end function

/* ======================================================== load_dates */
	public function load_dates($month = 99, $year = 99, $day = 99)
	{
		if ($this->month != 99)
			return;

		$not_this_month = FALSE;

		if ($month === 99)
		{
			$this->month = date('n');
		}
		else
		{
			$this->month = (int)$month;
			$not_this_month = TRUE;
		} //end if

		if ($year === 99)
		{
			$this->year = date('Y');
		}
		else
		{
			$this->year = (int)$year;
			$not_this_month = TRUE;
		} //end if

		if ($not_this_month)
		{
			if ($day !== 99)
				$this->today = $day;
			else
				$this->today = '1';
		}
		else
		{
			if ($day !== 99)
				$this->today = $day;
			else
				$this->today = date('j', mktime(0,0,0,$this->month,$day,$this->year));
		} //end if

		$this->unix_date = mktime(0,0,0,$this->month,$this->today,$this->year);
		
		$this->month_end_day = date('t', $this->unix_date);
		$this->unix_month_end_day = mktime(0,0,0,$this->month,$this->month_end_day,$this->year);
		$this->unix_month_beg_day = mktime(0,0,0,$this->month,1,$this->year);
		
		$this->mysql_date = date('Y-n-j', $this->unix_date);
		$this->mysql_month_beg_day = date('Y-n-1', $this->unix_date);
		$this->mysql_month_end_day = date('Y-n-t', $this->unix_date);
	} //end function _load_dates

/* ======================================================== get_calendar_data */
	public function get_calendar_data($month = 99, $year = 99)
	{
		if ($this->unix_date === '')
			$this->load_dates($month, $year);

		$query = $this->_get_events_between($this->mysql_month_beg_day, $this->mysql_month_end_day);

		$num_results = $query->num_rows();
		if ($num_results === 0)
			return array();

		$data = array();
		for ($i = 1; $i <= $this->month_end_day; $i++)
		{
			foreach ($query->result() as $row)
			{
				$event_beg_date = mysql_datetime_to_array($row->event_beginning_date);
				$event_end_date = mysql_datetime_to_array($row->event_end_date);
				
				$for_loop_date = mktime(0,0,0,$this->month,$i,$this->year);

				if (($event_beg_date[8] <= $for_loop_date) && ($event_end_date[8] >= $for_loop_date))
				{
					if (isset($data[$i]))
						$data[$i] .= "<a href='#" . url_title($row->title) . "' class='cluster-" . $row->cluster . "'><nobr>" . $row->title . "</nobr></a>";
					else
						$data[$i] = "<a href='#" . url_title($row->title) . "' class='cluster-" . $row->cluster . "'><nobr>" . $row->title . "</nobr></a>";
				} //end if
			} //end foreach
		} //end for

		$this->data = $data;

		return $this->data;
	} //end function get_calendar_data

/* ================================================= _get_events_between */
	private function _get_events_between($beg_date, $end_date, $limit = 10)
	{
		$now = date('Y-m-d');

		if ($beg_date != '' && $end_date == '')
		{
			//then we'll get everything > beg_date, limit 10
			$this->db->limit($limit);
			$this->db->where(array('event_end_date >=' => $beg_date));
		}
		elseif ($beg_date != '' && $end_date != '')
		{
			//then we want events between beg_date and end_date
			$this->db->where("(event_beginning_date >= '$beg_date' AND event_beginning_date <= '$end_date') OR (event_beginning_date <= '$beg_date' AND event_end_date <= $end_date) OR (event_beginning_date >= $beg_date AND event_end_date <= $end_date) OR (event_end_date >= '$beg_date' AND event_end_date <= '$end_date')");
		}
		else
		{
			//we'll get the next 10 upcoming events
			$this->db->where(array('event_end_date >=' => $now));
			$this->db->limit($limit);
		} //end if

		$this->db->order_by('event_beginning_date, event_beginning_time');
		$query = $this->db->get_where('events');

		return $query;
	} //end function _get_events_between

/* ====================================================== print_todays_events */
	public function print_todays_events($when = '')
	{
		if ($this->unix_date === '')
			$this->load_dates();

		if ($when === 'tomorrow')
		{
			$this->unix_date = $this->unix_date + (24*60*60);
			$this->mysql_date = date('Y-n-j', $this->unix_date);
		} //end if

		$this->db->where("(event_beginning_date <= {$this->mysql_date}) AND (event_end_date >= {$this->mysql_date})");
		$this->db->order_by('event_beginning_time');
		$query = $this->db->get('events');

		if ($query->num_rows() === 0)
			return 'There are no events scheduled.';

		$events = array();
		foreach($query->result() as $row)
		{
			if ($row->event_beginning_time !== '00:00:00')
				$time = format_time($row->event_beginning_time);
			else
				$time = '';

			$events[] = $time . " - " . $row->title;
		} //end foreach

		$event_string = implode('<br />', $events);

		return $event_string;
	} //end function print_todays_events

/* =================================================== list_events */
	public function list_events($beg_date = '', $end_date = '', $list_descriptions = FALSE) {
		
		$query = $this->_get_events_between($beg_date, $end_date);

		$calendar = "<table id='event-list'>\n";
		foreach ($query->result() as $event)
		{
			$beg_date = mysql_datetime_to_array($event->event_beginning_date);
			$end_date = mysql_datetime_to_array($event->event_end_date);

			if ($event->event_beginning_date != $event->event_end_date)
				$date = date('M j', $beg_date[8]) . " - " . date('M j', $end_date[8]);
			else
				$date = date('M j', $beg_date[8]);

			$calendar .= "<tr class='title-row'><td class='date'>" . $date . "</td><td class='title'>";

			if ($list_descriptions)
			{
				$calendar .= "<a name='" . url_title($event->title) . "'>" . $event->title . "</a>";
			}
			else
			{
				$calendar .= "<a href='" . site_url() . "school_calendar/#" . url_title($event->title) . "'>" . $event->title . "</a>";
			} //end if

			$calendar .= "</td></tr>\n";

			if ($list_descriptions && (($event->description != '' or $event->event_beginning_time != '00:00:00') or ($event->description != '' && $event->event_beginning_time != '00:00:00')))
			{
				$calendar .= "<tr class='description'><td>&nbsp;</td><td>";

				if ($event->event_beginning_time && $event->event_beginning_time != '00:00:00')
				{
					$calendar .= format_time($event->event_beginning_time);

					if ($event->event_end_time)
						$calendar .= " - " . format_time($event->event_end_time);

					$calendar .= br();
				} //end if

				$calendar .= $event->description;
				$calendar .= "</td></tr>\n";
			} //end if
		} //end foreach
		$calendar .= "</table>\n";

		return $calendar;
	} //end function get_calendar

/* ============================================ select_big_calendar_template */
	public function select_big_calendar_template()
	{
		$this->calendar_prefs['template'] = '

   {table_open}<table border="0" cellpadding="0" cellspacing="0" id="calendar">{/table_open}

   {heading_row_start}<tr>{/heading_row_start}

   {heading_previous_cell}<th><a href="{previous_url}">&lt;&lt;</a></th>{/heading_previous_cell}
   {heading_title_cell}<th colspan="{colspan}">{heading}</th>{/heading_title_cell}
   {heading_next_cell}<th><a href="{next_url}">&gt;&gt;</a></th>{/heading_next_cell}

   {heading_row_end}</tr>{/heading_row_end}

   {week_row_start}<tr class="weekdays">{/week_row_start}
   {week_day_cell}<td>{week_day}</td>{/week_day_cell}
   {week_row_end}</tr>{/week_row_end}

   {cal_row_start}<tr>{/cal_row_start}
   {cal_cell_start}<td>{/cal_cell_start}

   {cal_cell_content}<div class="highlight">{day}<br />{content}</div>{/cal_cell_content}
   {cal_cell_content_today}<div class="highlight-today">{day}<br />{content}</div>{/cal_cell_content_today}

   {cal_cell_no_content}{day}{/cal_cell_no_content}
   {cal_cell_no_content_today}<div class="highlight-today">{day}</div>{/cal_cell_no_content_today}

   {cal_cell_blank}<div class="empty">&nbsp;</div>{/cal_cell_blank}

   {cal_cell_end}</td>{/cal_cell_end}
   {cal_row_end}</tr>{/cal_row_end}

   {table_close}</table>{/table_close}
';

	return;
} //end function

} //end class