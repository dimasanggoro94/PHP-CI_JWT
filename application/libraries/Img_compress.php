<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Img_compress
{
	protected $ci;

	public function __construct()
	{
        $this->ci =& get_instance();
	}

	
	public function compress_ktp($id_user)	
	{
		$this->ci->db->where('id_user_client', $id_user);
		$data_user = $this->ci->db->get('user_client');
		$data_user = $data_user->row();

		$config['image_library'] = 'gd2';
		$config['source_image'] = './'.$data_user->id_foto;
		// $config['create_thumb'] = TRUE;
		// $config['dynamic_output'] = FALSE;

		$config['width'] = 800;
		$config['quality'] = '80';

		$this->ci->load->library('image_lib', $config);

		if ( ! $this->ci->image_lib->resize())
		{
			echo $this->ci->image_lib->display_errors();
		}

	}

	
	public function compress_sim($id_sim)	
	{
		$this->ci->db->where('id_sim', $id_sim);
		$data_sim = $this->ci->db->get('user_sim');
		$data_sim = $data_sim->row();

		$config['image_library'] = 'gd2';
		$config['source_image'] = './'.$data_sim->foto;
		// $config['create_thumb'] = TRUE;
		// $config['dynamic_output'] = FALSE;

		$config['width'] = 800;
		$config['quality'] = '80';

		$this->ci->load->library('image_lib', $config);

		if ( ! $this->ci->image_lib->resize())
		{
			echo $this->ci->image_lib->display_errors();
		}

	}

}

/* End of file Img_compress.php */
/* Location: ./application/libraries/Img_compress.php */
