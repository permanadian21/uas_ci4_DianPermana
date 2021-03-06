<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class ProdukController extends BaseController
{
	var $produk;

	public function __construct()
	{
		$this->produk = new \App\Models\Produk();
	}

	public function index()
	{
		$segment = $this->request->uri->getSegment(1);
		
		if($segment=='api'){
			return $this->getData();
		}elseif($segment =='app'){
			return $this->getAppData();
		}else{
			$data['page'] = 'pages/produk_view';
			return view('main',$data);
		}
	}

	private function getAppData()
	{
		$data = $this->produk->findAll();
		return $this->response->setJSON($data);
	}

	public function getdata(){
		$this->response->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Headers', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
		$filter = $this->request->getGet('keyword');
		$data = [];

		$produk 		= new \App\Models\Produk();
		$order 			= $this->request->getGet('order');
		$column 		= $this->request->getGet('columns');
		$column_order 	= $column[$order[0]['column']]['data'];
		$keyword 		= $this->request->getGet('search');
		$totalPerpage 	= $this->request->getGet('length');
		$start 			= $this->request->getGet('start');
		
		$pages = 1;
		if($start > 0){
			$pages = floor($start / $totalPerpage) + 1;
		}
		
		
		$total_data = count($produk->findAll());
		if($keyword['value'] !=''){
			$total_data = count($produk->orderBy($column_order, $order[0]['dir'])
				->like("nama_produk","%".$keyword['value']."%")
				->findAll());
		}

		$data = $produk->orderBy($column_order, $order[0]['dir'])
			->like("nama_produk","%".$keyword['value']."%")
			->paginate($totalPerpage, "group", $pages);

		return $this->response->setJSON([
			"data"=> $data,
			"draw"=> $this->request->getGet('draw'),
			"recordsTotal"=> $total_data,
			"recordsFiltered"=> $total_data
		]);
	}

	public function create(){

		$input = $this->request->getPost();
		$input['gambar'] = "-";
		try{
			$gambar = $this->request->getFile('gambar');
			$file_name = $gambar->getRandomName();
			$file_path = 'uploads';
			$gambar->move("./".$file_path,$file_name);
			$input['gambar'] = base_url()."/".$file_path."/".$file_name;
		}catch(\Exception $e){}

		if ($this->produk->save($input) === false)
		{
			return  $this->response->setStatusCode(422)
				->setJSON([$this->produk->errors()]);
		}else
			return $this->response->setJSON(["message"=>"data berhasil di tambahkan"]);
	}

	public function show($id){
		$this->response->setHeader('Access-Control-Allow-Origin', '*')
            ->setHeader('Access-Control-Allow-Headers', '*')
            ->setHeader('Access-Control-Allow-Methods', 'GET, POST, OPTIONS, PUT, DELETE');
			
		return $this->response->setJSON($this->produk->find($id));
	}

	public function update($id){
		$produkSelect = $this->produk->find($id);

		$input = $this->request->getRawInput();
		$input['id'] = $id;

		// $input['gambar'] = $produkSelect['gambar'];
		// try{
		// 	$gambar = $this->request->getFile('gambar');
		// 	if($gambar){
		// 		$file_name = $gambar->getRandomName();
		// 		$file_path = 'uploads';
		// 		$gambar->move("./".$file_path,$file_name);
		// 		$input['gambar'] = base_url()."/".$file_path."/".$file_name;
		// 	}
		// }catch(\Exception $e){}
		
		if ($this->produk->save($input) === false)
		{
			return  $this->response->setStatusCode(422)
				->setJSON([$this->produk->errors()]);
		}else
			return $this->response->setJSON(["message"=>"data berhasil di perbaharui"]);
	}

	public function delete($id){
		$this->produk->delete($id);
		return $this->response->setJSON(["message"=>"data berhasil di hapus"]);
	}

}
