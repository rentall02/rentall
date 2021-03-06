<?php

class MBeranda extends CI_Model{

    public function getKat(){
        $this->db->select('kategori');
        $this->db->group_by('kategori');
        return $this->db->get('kategori')->result_array();
    }

    public function getSubKat(){
        $this->db->select('sub_kat');
        return $this->db->get('kategori')->result_array();
    }

    public function getAllKategori()
    {   
        return $this->db->get('kategori')->result_array();
    }

    public function getProduct($id)
    {   
        $this->db->where('id_kategori', $id);
        return $this->db->get('items')->result_array();
    }

    public function getNewProduct()
    {   
        $this->db->select('*');
        $this->db->from('items');
        $this->db->join('kategori', 'items.id_kategori = kategori.id_kategori');
        $this->db->order_by('id_item', 'DESC');
        $this->db->limit(8);
        $query = $this->db->get()->result_array();
        return $query;
    }


    public function getAllProduct()
    {   
        return $this->db->get('items')->result_array();
    }

    public function getDetail($id)
    {   
        $this->db->where('prod_id', $id);
        return $this->db->get('items')->result_array();
    }

    public function addTC()
    {
        $id_product = $this->input->post('id', true);
        $durasi = $this->input->post('durasi', true);
        $username = $this->session->userdata('username');

        $id_user = $this->session->userdata('id');

		$rows = $this->db->query('select * from cart where prod_id ="'.$id_product.'" and id_user = "'.$id_user.'"');
        if ($rows->num_rows() == 1) {
            $product = $rows->row();
            $qty = $product->qty + $durasi;
            $data = array(
                    'qty' => $qty
            );
            $this->db->where('prod_id', $id_product);
            $this->db->update('cart', $data);
        } else {
            $data = array(
                'prod_id' => $id_product,
                'qty' => $durasi,
                'id_user' => $id_user
            );
            
            $this->db->insert('cart', $data);  
        }

        redirect('beranda/keranjang');
    }

    public function getATC()
    {
        $id_user = $this->session->userdata('id');

        $this->db->where('id_user', $id_user);
        return $this->db->get('cart')->result_array();
    }

    public function getTotal()
    {
        $id_user = $this->session->userdata('id');
        $rows = $this->db->query('select sum( harga * durasi * qty) as total from items, cart where items.id_item = cart.id_item and cart.id_user = "' . $id_user . '"');
        $price = $rows->row();
        return $harga = $price->total;
    }

    public function konfirPembayaran()
    {
        $id_user = $this->session->userdata('id');
        $id_order = $this->input->post('id_order', true);

        $config = array(
            'upload_path' => "./assets/img/pembayaran/",
            'allowed_types' => "gif|jpg|png|jpeg",
            'overwrite' => TRUE,
            'file_name' => $id_order ."_". $id_user . ".jpeg"
        );

        $this->load->library('upload', $config);
        $this->upload->initialize($config);
        $this->upload->do_upload('upload_image');

        $data['errors'] = $this->upload->display_errors('<p>', '</p>');
        $data['result'] = print_r($this->upload->data(), true);
        $data['files']  = print_r($_FILES, true);
        $data['post']   = print_r($_POST, true);

        if ($data['errors'] = $this->upload->display_errors('<p>', '</p>')) {
            $this->session->set_flashdata('error', $this->upload->display_errors('<p>', '</p>'));
            redirect('order/penyewaanRenter');
        } else {
            $data = array(
                'id_order' => $id_order,
                'rekening' => $this->input->post('nomor_rekening', true),
                'an' => $this->input->post('nama', true),
                'bank' => $this->input->post('bank', true),
                'jumlah_bayar' => $this->input->post('jumlah_bayar', true),
                'foto' => $config['file_name']

            );
            $this->db->insert('pembayaran', $data);

            $this->db->set('id_pembayaran', 3);
            $this->db->where('id_order', $id_order);
            $this->db->update('order_item');  
            redirect('order/penyewaanRenter');
        }

    }

    public function co()
    {
        $id_user = $this->session->userdata('id');

		$row = $this->db->query('select max(id_order) as id_order from order_item');
		$id_order = $row->row();
        $nomor = $id_order->id_order;
        $no_order = 1;
        if($nomor == 0){
            $no_orderf= $no_order;
        } else {
            $no_orderf= $nomor+1;
        }

        if ($this->input->post('pembayaran', true) == "ditempat") {
            $pembayaran = 1;
        } else {
            $pembayaran = 2;
        }


        if ($this->input->post('antar', true) == "antar") {
            $antar = 1;
        } else {
            $antar = 0;
        }
        
        $query = $this->db->query('select * from cart where id_user = "'.$id_user.'"')->result_array();

        foreach ($query as $q ) {
            $datetime1 = date_create($q['tgl_sewa']);
            $datetime2 = date_create($q['tgl_kembali']);
            $durasi = date_diff($datetime1, $datetime2)->format('%a');
            
            $id_item = $q['id_item'];
            $row = $this->db->query("select id_user from items where id_item = $id_item")->row();
            $data = array(
                'id_order' => $no_orderf, 
                'id_user' => $id_user,
                'status' => 0,
                'id_vendor' => $row->id_user,
                'tgl_sewa' => $q['tgl_sewa'],
                'tgl_kembali' => $q['tgl_kembali'],
                'id_pembayaran' => $pembayaran,
                'antar' => $antar
            );

            $this->db->insert('order_item', $data);
            
            $data_d = array(
                'id_order' => $no_orderf,
                'id_item' => $id_item,
                'qty' => $q['qty'],
                'durasi_sewa' => $durasi
            );
            $this->db->insert('order_detail', $data_d);
        }
        $total_bayar = $this->getTotal(); 
        $this->db->where('id_user', $id_user);
        $this->db->delete('cart');
        if ($pembayaran == 0) {
            $this->session->set_flashdata('success', 'Silahkan lakukan pembayaran saat pengambilan barang sebesar Rp. '.number_format($total_bayar,0,",",".").' ');
        } else {
            $this->session->set_flashdata('success', 'Silahkan lakukan pembayaran sebesar Rp. '.number_format($total_bayar,0,",",".").' ');
        }
        redirect('order/penyewaanRenter');
    }

    public function getOrder()
    {
        $id_user = $this->session->userdata('id');

        $this->db->where('cust_id', $id_user);
        return $this->db->get('orders')->result_array();
    }
}