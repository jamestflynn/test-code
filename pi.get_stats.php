<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

$plugin_info = array(
  'pi_name'         => 'Quick Statistics',
  'pi_version'      => '1.0',
  'pi_author'       => 'James Flynn',
  'pi_author_url'   => 'http://example.com/',
  'pi_description'  => 'Returns a list of Statistics',
  'pi_usage'        => Get_Stats::usage()
);

class Get_Stats
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
    	//$mem_url = $this->EE->TMPL->fetch_param('mem_id', '');
		$parameter = $this->EE->TMPL->fetch_param('type', '');
		$end = mktime(); 		
		
		//$row = $query3->row()->entry_id;
		//var_dump($mem_id);
		//exit;
		
		$pledgeChannelID = 16;
		$charityChannelID = 17;

		/* SUM OF ALL PLEDGES MADE */
		$this->EE->db->select_sum('field_id_119');			
        $query_pledge_sum = $this->EE->db->get('exp_channel_data');
		$pl_sum = $query_pledge_sum->row()->field_id_119;

		/* SUM OF INITIAL WEIGHT */
		$this->EE->db->select_sum('field_id_106');			
        $query_pledge_sum = $this->EE->db->get('exp_channel_data');
		$chal_start_weight = $query_pledge_sum->row()->field_id_106;		

		/* SUM OF GOAL/CURRENT WEIGHT */
		$this->EE->db->select_sum('field_id_107');			
        $query_pledge_sum = $this->EE->db->get('exp_channel_data');
		$chal_goal_weight = $query_pledge_sum->row()->field_id_107;					

		/* TOTAL NUMBER OF CHARITIES AVAILABLE */
		$this->EE->db->where('channel_id', '17');
		$char_count = number_format($this->EE->db->from('exp_channel_titles')->count_all_results());
		$char_total_loss = $chal_start_weight - $chal_goal_weight;
		$char_total_loss = number_format($char_total_loss);

		$pledge_total = $pl_sum * $char_total_loss;
		$pledge_total = $pledge_total;



		$this->EE->db->select('ct.entry_id, ct.entry_date, ct.expiration_date, ct.title, ct.url_title, 
							  cd.field_id_105, cd.field_id_106, cd.field_id_136 as youtube_id, cd.field_id_107 as goal_weight, cd.field_id_130, cd.field_id_131 as chal_photo,  cd.field_id_132 as chal_story,  cd.entry_id as chal_id, cd.field_id_112 as final_weight,
							  ct2.title as char_title,
							  ct3.url_title as member_url,
							  cc.field_id_113 as char_ein, cc.field_id_114 as char_url, cc.field_id_115 as char_description,
							  cm.field_id_128 as profile_fname,cm.field_id_129 as profile_lname,,cm.field_id_133 as profile_city,,cm.field_id_134 as profile_state');			
		$this->EE->db->from('exp_channel_titles ct');
		$this->EE->db->join('exp_channel_data cd', 'cd.entry_id = ct.entry_id','left');
		$this->EE->db->join('exp_channel_titles ct2', 'ct2.entry_id = cd.field_id_130','left');		
		$this->EE->db->join('exp_channel_titles ct3', 'ct3.entry_id = cd.field_id_105','left');				
		$this->EE->db->join('exp_channel_data cm', 'cd.field_id_105 = cm.entry_id','left');				
		$this->EE->db->join('exp_channel_data cc', 'cd.field_id_130 = cc.entry_id','left');						
		$this->EE->db->where('cd.field_id_106 >=', 1);
		//$this->EE->db->where('ct.expiration_date >=', $end);	
		$this->EE->db->order_by("ct.expiration_date", "desc"); 
        $query = $this->EE->db->get();

		/*CHARITY QUERY*/
		$this->EE->db->select('ct.entry_id, ct.entry_date, ct.expiration_date, ct.title, ct.url_title, 
							  cd.field_id_105, cd.field_id_106, cd.field_id_136 as youtube_id, cd.field_id_107 as goal_weight, cd.field_id_130, cd.field_id_131 as chal_photo,  cd.field_id_132 as chal_story,  cd.entry_id as chal_id, cd.field_id_112 as final_weight,
							  ct2.title as char_title,ct2.entry_id as char_id,
							  ct3.url_title as member_url,
							  cc.field_id_113 as char_ein, cc.field_id_114 as char_url, cc.field_id_115 as char_description,
							  cm.field_id_128 as profile_fname,cm.field_id_129 as profile_lname,,cm.field_id_133 as profile_city,,cm.field_id_134 as profile_state');			
		$this->EE->db->from('exp_channel_titles ct');
		$this->EE->db->join('exp_channel_data cd', 'cd.entry_id = ct.entry_id','left');
		$this->EE->db->join('exp_channel_titles ct2', 'ct2.entry_id = cd.field_id_130','left');		
		$this->EE->db->join('exp_channel_titles ct3', 'ct3.entry_id = cd.field_id_105','left');				
		$this->EE->db->join('exp_channel_data cm', 'cd.field_id_105 = cm.entry_id','left');				
		$this->EE->db->join('exp_channel_data cc', 'cd.field_id_130 = cc.entry_id','left');						
		$this->EE->db->where('cd.field_id_106 >=', 1);
		//$this->EE->db->where('ct.expiration_date >=', $end);	
		$this->EE->db->order_by("char_title", "desc"); 
		$this->EE->db->group_by("char_title"); 
        $query2 = $this->EE->db->get();        



        switch ($parameter)
		{

			case "stats":

			  $this->EE->load->library('typography');
			  $this->EE->typography->parse_images = TRUE;
			  $total_pledge_amount = 0;
			  $dollars_pledged = 0;			  
			




			  foreach($query->result_array() as $row)
			  {				 
			  		$members_loop[] = array('goal_weight' => $row['goal_weight'],
			  						   		'profile_fname' => $row['profile_fname'],
			  						   		'profile_lname' => $row['profile_lname'],
			  						   		'profile_state' => $row['profile_state'],
											'url_title' => $row['member_url']
			  						   		);
			  }


			  foreach($query2->result_array() as $row)
			  {				 

			        $this->EE->db->select('ct.entry_id, ct.entry_date, ct.expiration_date, ct.title, ct.url_title,
										  cd.field_id_116 ,cd.field_id_119,cd.field_id_122,
										  cm.field_id_128 as pl_fname,cm.field_id_129 as pl_lname,cm.field_id_133 as pl_city,cm.field_id_134 as pl_state');			
					$this->EE->db->from('exp_channel_titles ct');
					$this->EE->db->join('exp_channel_data cd', 'cd.entry_id = ct.entry_id','left');			
					$this->EE->db->join('exp_channel_data cm', 'cd.field_id_116 = cm.entry_id','left');				
					$this->EE->db->where('cd.field_id_118', $row['chal_id'] );
					$this->EE->db->order_by("ct.entry_date", "desc"); 
			        $query3 = $this->EE->db->get();

					$dollars_pledged_subtotal = 0;
					
					foreach($query3->result_array() as $row2)
					{				 
						  
					  		$dollars_pledged_subtotal = $dollars_pledged_subtotal + $row2['field_id_119'];
					}	


			  		$charity_loop[] = array('char_title' => $row['char_title'],
			  								'dollars_pledged' => $dollars_pledged_subtotal
			  						   		);
			  }

							  $variable_row = array(
                                'pledge_count'          	=> $pl_sum,
                                'char_count'            	=> $char_count,
                                'chal_start_weight'         => $chal_start_weight,
                                'members_loop' 					=> $members_loop,
                                'charity_loop' 					=> $charity_loop,
                                'chal_goal_weight'          => $chal_goal_weight,
                                'char_total_loss'           => $char_total_loss,
                                'pledge_total'           	=> $pledge_total
                                );
				  
				  
				  $variables[] = $variable_row;

			  
			  $this->return_data = $this->EE->TMPL->parse_variables($this->EE->TMPL->tagdata, $variables);
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
/* End of file pi.get_stats.php */
/* Location: ./system/expressionengine/third_party/get_stats/pi.get_stats.php */