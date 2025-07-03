<?php

namespace App\Controllers;

use App\Models\TransactionModel;
use App\Models\TransactionDetailModel;

class TransaksiController extends BaseController
{
    protected $cart;
    protected$client;
    protected$apikey;
    protected$transaction;
    protected$transaction_detail;

    function __construct()
    {
        helper('number');
        helper('form');
        $this->cart = \Config\Services::cart();
        $this->client = new \GuzzleHttp\Client();
        $this->apiKey = env('COST_KEY');
        $this->transaction = new TransactionModel();
        $this->transaction_detail = new TransactionDetailModel();
    }

    public function index()
    {
        $data['items'] = $this->cart->contents();
        $data['total'] = $this->cart->total();
        return view('v_keranjang', $data);
    }

    public function cart_add()
    {
        $this->cart->insert(array(
            'id'        => $this->request->getPost('id'),
            'qty'       => 1,
            'price'     => $this->request->getPost('harga'),
            'name'      => $this->request->getPost('nama'),
            'options'   => array('foto' => $this->request->getPost('foto'))
        ));
        session()->setflashdata('success', 'Produk berhasil ditambahkan ke keranjang. (<a href="' . base_url() . 'keranjang">Lihat</a>)');
        return redirect()->to(base_url('/'));
    }

    public function cart_clear()
    {
        $this->cart->destroy();
        session()->setflashdata('success', 'Keranjang Berhasil Dikosongkan');
        return redirect()->to(base_url('keranjang'));
    }

    public function cart_edit()
    {
        $i = 1;
        foreach ($this->cart->contents() as $value) {
            $this->cart->update(array(
                'rowid' => $value['rowid'],
                'qty'   => $this->request->getPost('qty' . $i++)
            ));
        }

        session()->setflashdata('success', 'Keranjang Berhasil Diedit');
        return redirect()->to(base_url('keranjang'));
    }

    public function cart_delete($rowid)
    {
        $this->cart->remove($rowid);
        session()->setflashdata('success', 'Keranjang Berhasil Dihapus');
        return redirect()->to(base_url('keranjang'));
    }

    public function checkout()
{
    $data['items'] = $this->cart->contents();
    $data['total'] = $this->cart->total();

    return view('v_checkout', $data);
}

    public function getLocation()
{
		
    $search = $this->request->getGet('search');

    $response = $this->client->request(
        'GET', 
        'https://rajaongkir.komerce.id/api/v1/destination/domestic-destination?search='.$search.'&limit=50', [
            'headers' => [
                'accept' => 'application/json',
                'key' => $this->apiKey,
            ],
        ]
    );

    $body = json_decode($response->getBody(), true); 
    return $this->response->setJSON($body['data']);
}

public function getCost()
{ 
	
    $destination = $this->request->getGet('destination');

    $response = $this->client->request(
        'POST', 
        'https://rajaongkir.komerce.id/api/v1/calculate/domestic-cost', [
            'multipart' => [
                [
                    'name' => 'origin',
                    'contents' => '64999'
                ],
                [
                    'name' => 'destination',
                    'contents' => $destination
                ],
                [
                    'name' => 'weight',
                    'contents' => '1000'
                ],
                [
                    'name' => 'courier',
                    'contents' => 'jne'
                ]
            ],
            'headers' => [
                'accept' => 'application/json',
                'key' => $this->apiKey,
            ],
        ]
    );

    $body = json_decode($response->getBody(), true); 
    return $this->response->setJSON($body['data']);
}
    public function buy()
    {
        if ($this->request->getPost()) {
            

           
            $subtotal = $this->cart->total();
            
           
            $ongkir = (float) $this->request->getPost('ongkir');

           
            $biaya_admin = 0;
            $biaya_admin = 0;
            if ($subtotal <= 20000000) {
                $biaya_admin = $subtotal * 0.006; // 0.6%
            } elseif ($subtotal <= 40000000) {
                $biaya_admin = $subtotal * 0.008; // 0.8%
            } else { // $subtotal > 40000000
                $biaya_admin = $subtotal * 0.01;  // 1%
            }

            // Kalkulasi PPN (11%) dan definisikan variabelnya
            $ppn = $subtotal * 0.11;

            // Kalkulasi Grand Total dan definisikan variabelnya
            $grand_total = $subtotal + $ongkir + $biaya_admin + $ppn;

            $dataForm = [
                'username'      => session()->get('username'),
                'total_harga'   => $grand_total,
                'alamat'        => $this->request->getPost('alamat'),
                'ongkir'        => $ongkir,
                'ppn'           => $ppn,
                'biaya_admin'   => $biaya_admin,
                'status'        => 0,
                'created_at'    => date("Y-m-d H:i:s"),
                'updated_at'    => date("Y-m-d H:i:s")
            ];

            $this->transaction->insert($dataForm);

            $last_insert_id = $this->transaction->getInsertID();

         
            foreach ($this->cart->contents() as $value) {
                $dataFormDetail = [
                    'transaction_id' => $last_insert_id,
                    'product_id'     => $value['id'],
                    'jumlah'         => $value['qty'],
                    'diskon'         => 0,
                    'subtotal_harga' => $value['qty'] * $value['price'],
                    'created_at'     => date("Y-m-d H:i:s"),
                    'updated_at'     => date("Y-m-d H:i:s")
                ];

                $this->transaction_detail->insert($dataFormDetail);
            }

           
            $this->cart->destroy();
    
            session()->setflashdata('success', 'Pesanan Anda berhasil dibuat.');
            return redirect()->to(base_url('keranjang'));
        }
    }
}