<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Awal extends CI_Controller
{
    public function index()
    {
        $data['title'] = 'Halaman Utama | WarMoed';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        //PAGINATIONNYA
        $this->load->library('pagination');
        //CONFIG
        $config['base_url'] = 'http://localhost/kuliah/warung/awal/index';
        $config['total_rows'] = $this->db->get_where('menu')->num_rows();
        $config['per_page'] = 3;
        //styleing dulu
        $config['full_tag_open'] = '<nav><ul class="pagination pagination-lg">';
        $config['full_tag_close'] = ' </ul></nav>';

        $config['first_link'] = 'First';
        $config['first_tag_open']  = '<li class="page-item">';
        $config['first_tag_close']  = '</li>';

        $config['last_link'] = 'Last';
        $config['last_tag_open']  = '<li class="page-item">';
        $config['last_tag_close']  = '</li>';

        $config['next_link'] = '&raquo';
        $config['next_tag_open']  = '<li class="page-item">';
        $config['next_tag_close']  = '</li>';

        $config['prev_link'] = '&laquo';
        $config['prev_tag_open']  = '<li class="page-item">';
        $config['prev_tag_close']  = '</li>';

        $config['cur_tag_open']  = '<li class="page-item active">  <a class="page-link" href="#">';
        $config['cur_tag_close']  = '</a></li>';

        $config['num_tag_open']  = '<li class="page-item">';
        $config['num_tag_close']  = '</li>';

        $config['attributes'] = array('class' => 'page-link');

        $this->pagination->initialize($config);


        $data['start'] = $this->uri->segment(3);

        $data['menu'] = $this->db->limit($config['per_page'], $data['start'])->get('menu')->result_array();

        if ($data['user']) {
            $data['chart'] = $this->db->from('keranjang')->join('menu', 'menu.id_menu=keranjang.id_produk')->where('status', '0')->where('id_user', $data['user']['id'])->get()->result_array();
            $data['jumlah'] = $this->db->select_sum('jumlah')->from('keranjang')->join('menu', 'menu.id_menu=keranjang.id_produk')->where('status', '0')->where('id_user', $data['user']['id'])->get()->row_array();
            $data['total'] = $this->db->select_sum('total_harga')->from('keranjang')->join('menu', 'menu.id_menu=keranjang.id_produk')->where('status', '0')->where('id_user', $data['user']['id'])->get()->row_array();
        }
        $this->load->view('templates/header', $data);
        $this->load->view('index', $data);
        $this->load->view('templates/footer');
    }

    public function tambah($id)
    {
        $user = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $menu = $this->db->get_where('menu', ['id_menu' => $id])->row_array();
        $cek = $this->db->get_where('keranjang', ['id_produk' => $id])->row_array();
        $data = [
            'id_produk' => $id,
            'id_user' => $user['id'],
            'jumlah' => 1,
            'total_harga' => $menu['harga'] * 1,
            'status' => 0,
            'date_created' => time()
        ];
        if (!$cek) {
            $this->db->insert('keranjang', $data);
        } else {
            $updatenya = [
                'jumlah' => $cek['jumlah'] + 1,
                'total_harga' => $cek['total_harga'] + $menu['harga']
            ];
            $this->db->set($updatenya);
            $this->db->where('id_produk', $id);
            $this->db->update('keranjang');
        }
        redirect('awal');
        $this->session->set_flashdata('message', '<div class="alert alert-success" role="alert">Item berhasih ditambahkan !</div>');
        redirect('awal');
    }

    public function keranjang()
    {
        $data['title'] = 'Halaman Keranjang | WarMoed';
        $data['user'] = $this->db->get_where('user', ['email' => $this->session->userdata('email')])->row_array();
        $data['menu'] = $this->db->get('menu')->result_array();
        if ($data['user']) {

            $data['chart'] = $this->db->from('keranjang')->join('menu', 'menu.id_menu=keranjang.id_produk')->where('status', '0')->where('id_user', $data['user']['id'])->get()->result_array();
            $data['jumlah'] = $this->db->select_sum('jumlah')->from('keranjang')->join('menu', 'menu.id_menu=keranjang.id_produk')->where('status', '0')->where('id_user', $data['user']['id'])->get()->row_array();
            $data['total'] = $this->db->select_sum('total_harga')->from('keranjang')->join('menu', 'menu.id_menu=keranjang.id_produk')->where('status', '0')->where('id_user', $data['user']['id'])->get()->row_array();
        }

        $this->load->view('templates/header', $data);
        $this->load->view('keranjang', $data);
        $this->load->view('templates/footer');
    }

    public function hapus_item($id)
    {
        $this->db->delete('keranjang', ['id' => $id]);
        redirect('awal/keranjang');
    }
}
