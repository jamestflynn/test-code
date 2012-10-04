<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
  'pi_name'         => 'Challenges List',
  'pi_version'      => '1.0',
  'pi_author'       => 'James Flynn',
  'pi_author_url'   => 'http://example.com/',
  'pi_description'  => 'Returns a list of challenges',
  'pi_usage'        => Get_Challenges::usage()
);

class Get_Challenges
{
    public $return_data = "";

    // --------------------------------------------------------------------

    /**
     * Memberlist
     *
     * This function returns a list of members
     *
     * @access  public
     * @return  string
     */
    public function __construct()
    {
        $this->EE =& get_instance();
		
		//Get parameters
    	$mem_url = $this->EE->TMPL->fetch_param('mem_id', '');
		$parameter = $this->EE->TMPL->fetch_param('type', '');
		$end = mktime(); 
		
        $this->EE->db->select('entry_id');			
		$this->EE->db->from('exp_channel_titles');	
		$this->EE->db->where('url_title', $mem_url);
        $query3 = $this->EE->db->get('',1);
		
		$row = $query3->row();
		$mem_id = $row->entry_id;
		
		
		//$row = $query3->row()->entry_id;
		//var_dump($mem_id);
		//exit;
		
		$this->EE->db->select('ct.entry_id, ct.entry_date, ct.expiration_date, ct.title, ct.url_title, 
							  cd.field_id_105, cd.field_id_106 as initial_weight, cd.field_id_136 as youtube_id, cd.field_id_107 as goal_weight, cd.field_id_130, cd.field_id_131 as chal_photo,  cd.field_id_132 as chal_story,  cd.entry_id as chal_id, cd.field_id_112 as final_weight,
							  ct2.title as char_title,
							  cc.field_id_113 as char_ein, cc.field_id_114 as char_url, cc.field_id_115 as char_description,
							  cm.field_id_128 as profile_fname,cm.field_id_129 as profile_lname');			
		$this->EE->db->from('exp_channel_titles ct');
		$this->EE->db->join('exp_channel_data cd', 'cd.entry_id = ct.entry_id','left');
		$this->EE->db->join('exp_channel_titles ct2', 'ct2.entry_id = cd.field_id_130','left');		
		$this->EE->db->join('exp_channel_data cm', 'cd.field_id_105 = cm.entry_id','left');				
		$this->EE->db->join('exp_channel_data cc', 'cd.field_id_130 = cc.entry_id','left');						
		$this->EE->db->where('cd.field_id_105', $mem_id);
		//$this->EE->db->where('ct.expiration_date >=', $end);	
		$this->EE->db->order_by("ct.expiration_date", "desc"); 
        $query = $this->EE->db->get('',1);
		
		if ($query->num_rows() > 0)
		{
			$chal_id = $query->row()->entry_id;
			
		}
		else 
		{
			$chal_id = 0;
		}	

        $this->EE->db->select('ct.entry_id, ct.entry_date, ct.expiration_date, ct.title, ct.url_title,
							  cd.field_id_116 ,cd.field_id_119,cd.field_id_122,
							  cm.field_id_128 as pl_fname,cm.field_id_129 as pl_lname,cm.field_id_133 as pl_city,cm.field_id_134 as pl_state');			
		$this->EE->db->from('exp_channel_titles ct');
		$this->EE->db->join('exp_channel_data cd', 'cd.entry_id = ct.entry_id','left');			
		$this->EE->db->join('exp_channel_data cm', 'cd.field_id_116 = cm.entry_id','left');				
		$this->EE->db->where('cd.field_id_118', $chal_id );
		//$this->EE->db->where('ct.expiration_date >=', $end);	
		$this->EE->db->order_by("ct.entry_date", "desc"); 
        $query2 = $this->EE->db->get();


        switch ($parameter)
		{

			case "charity":

			  foreach($query->result_array() as $row)
			  {
				  $this->return_data .= $row['char_title'];		
				  $this->return_data .= "<br />";
			  }
			  break;			  
			  
			case "single":

			  $this->EE->load->library('typography');
			  $this->EE->typography->parse_images = TRUE;
			  $total_pledge_amount = 0;
			  $dollars_pledged = 0;
			  foreach($query2->result_array() as $row)
			  {				 
				  
			  		$pledges[] = array('pl_auth_id' => $row['field_id_116'],
			  						   'pl_end_date' => $row['expiration_date'], 
			  						   'pl_title' => $row['title'], 'pl_amount' => $row['field_id_119'], 
			  						   'pl_comment' => $row['field_id_122'], 
			  						   'pl_fname' => $row['pl_fname'], 
			  						   'pl_lname' => $row['pl_lname'], 
									   'pl_city' => $row['pl_city'], 
			  						   'pl_state' => $row['pl_state'], 			  						   
			  						   'pl_pledge_date' => $row['entry_date']);
				     $dollars_pledged = $dollars_pledged + $row['field_id_119'];
			  }

			  foreach($query->result_array() as $row)
			  {	
			      if ($row['expiration_date'] >= $end) {
					  $chal_status = "Active";
					  $days_remaining = unixtojd($row['expiration_date']) - unixtojd($row['entry_date']);
					  }
			      if ($row['expiration_date'] < $end) {$chal_status = "Complete";$days_remaining = "0";}	
				  
				  $current_weightloss = $row['initial_weight'] - $row['final_weight'];
				  $goal_weightloss = $row['initial_weight'] - $row['goal_weight'];
				  $percentage_weightloss_division = $current_weightloss /$goal_weightloss;
				  $percentage_weightloss = round($percentage_weightloss_division * 100);				  
				  $total_pledge_amount = $current_weightloss * $dollars_pledged;
				  
				  $variable_row = array(
                                'chal_id'          => $row['chal_id'],
                                'start_date'          => $row['entry_date'],
                                'end_date'          => $row['expiration_date'],								
								'the_title'          => $row['title'],
								'the_photo'          => $this->EE->typography->parse_file_paths($row['chal_photo']),								
                                'char_title'           => $row['char_title'],
								'char_ein'           => $row['char_ein'],
								'char_url'           => $row['char_url'],
								'char_description'           => $row['char_description'],
                                'initial_weight'          => $row['initial_weight'],
                                'goal_weight'   => $row['goal_weight'],
								'current_weightloss'  => $current_weightloss,
								'goal_weightloss' => $goal_weightloss,
								'percentage_weightloss' => $percentage_weightloss,
								'days_remaining' => $days_remaining,
								'youtube_id' 	=> $row['youtube_id'],
                                'profile_fname'   => $row['profile_fname'],
                                'profile_lname'   => $row['profile_lname'],
								'pledges' 		=> $pledges,
								'chal_status' => $chal_status,
								'total_pledges' => count($pledges),
								'dollars_pledged' => number_format($dollars_pledged, 2),
								'total_pledge_amount' => number_format($total_pledge_amount, 2)
                                );
				  
				  $type_prefs = array('text_format' => 'xhtml', 'html_format' => 'all');
			      $variable_row['the_story'] = array($row['chal_story'], $type_prefs);
				  $variables[] = $variable_row;
				  
			  }
			  $this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);
			  break;
			  
			case "list":  
			
			  $this->return_data .= '<ul>';
			  foreach($query->result_array() as $row)
			  {
				  $this->return_data .= '<li>';
				  $this->return_data .= $row['title'];
				  //$this->return_data .= $row['initial_weight'];	
				 // $this->return_data .= $row['initial_weight'];	
				  $this->return_data .= "<br />";
			  }
			  $this->return_data .= '</ul>';
			  break;
		}
    }

    // --------------------------------------------------------------------

    /**
     * Usage
     *
     * This function describes how the plugin is used.
     *
     * @access  public
     * @return  string
     */
    public static function usage()
    {
        ob_start();  ?>

The Get Challenges Plugin simply outputs a
list of 15 challenges by a member.

    {exp:Get_Challenges member_id=""}

This is an incredibly simple Plugin.


    <?php
        $buffer = ob_get_contents();
        ob_end_clean();

        return $buffer;
    }
    // END
}
/* End of file pi.get_challenges.php */
/* Location: ./system/expressionengine/third_party/memberlist/pi.memberlist.php */