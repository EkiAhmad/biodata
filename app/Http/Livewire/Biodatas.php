<?php

namespace App\Http\Livewire;

use Livewire\Component;
use App\Models\Biodata;
use Livewire\WithFileUploads;
use Storage;
use File;

class Biodatas extends Component
{
	public $biodatas, $nama_file, $path, $img, $data_id, $state, $data_img, $name, $email, $date, $phone, $gender;
    public $isModal = 0;
    use WithFileUploads;

    public function render()
    {
        $this->biodatas = Biodata::orderBy('created_at', 'DESC')->get(); 
        return view('livewire.biodatas');
    }

    public function create()
    {
        $this->resetFields();
    	$this->state = 'create';
        $this->openModal();
    }

    public function closeModal()
    {
    	$this->resetFields();
        $this->isModal = false;
    }

    public function openModal()
    {
        $this->isModal = true;
    }

    public function resetFields()
    {
        $this->nama_file = '';
        $this->path = '';
        $this->img = '';
        $this->data_id = '';
    }

    public function store()
    {
        $this->validate([
            'nama_file' => 'string',
            'path' => 'string',
            'name' => 'string',
            'email' => 'string',
            'phone' => 'string',
            'date' => 'string',
            'gender' => 'string',
            'img' => 'mimes:jpeg,png,jpg,gif,svg|max:1024'
        ]);

        $name = str_replace(' ', '_', $this->name);

        $nama_file = $name.'-'.date('YmdHis');

        if (!empty($this->img)) {
        	$pic = 'foto-'.$name.'-'.date('YmdHis').'.jpg';
        	$path = $this->img->storeAs('photo', $pic);
        	@Storage::delete(Biodata::find($this->data_id)->path);
        } else {	
        	$pic = $this->data_img;
        	$path = $this->path;
        }
        
        if ($this->data_id) {
        	// dd('files/'.Biodata::find($this->data_id)->nama_file.'.txt');
    		@Storage::delete('files/'.Biodata::find($this->data_id)->nama_file.'.txt');
    	}
    	// dd('der');
        // $dataValid['img'] = $this->img->store('img', 'public');
  
        // Biodata::create($dataValid);
        // @File::delete('app/files'.Biodata::find(storage_path($this->data_id)->nama_file).'.txt');
        Biodata::updateOrCreate(['id' => $this->data_id], [
            'nama_file' => $nama_file,
            'path' => $path,
            'img' => $pic,
            // 'img' => $this->img->store('todos', 'public'),
        ]);
    	
    	Storage::disk('local')->put('files/'.$nama_file.'.txt', $name.','.$this->email.','.$this->date.','.$this->phone.','.$this->gender.','.$pic);
        // if ($this->data_id) {
        // }

        session()->flash('message', $this->data_id ? $this->name . ' Edited': $this->name . ' Added');
        $this->closeModal();
        $this->resetFields();
    }

    public function edit($id)
    {
    	if (is_null($id)) {
    		dd('data not found')
    	}
        $bio = Biodata::find($id);
        $content = File::get(storage_path('app/files/'.$bio->nama_file.'.txt'));
        $data = explode(',', $content);

        // if ($content) {
        // 	dd('file not found');
        // }

        $this->data_id = $id;
        $this->nama_file = $bio->nama_file;
        $this->path = $bio->path;
        $this->data_img = $bio->img;
        $this->state = 'edit';

        $name = str_replace('_', ' ', $data[0]);
        $this->name = $name;
        $this->email = $data[1];
        $this->date = $data[2];
        $this->phone = $data[3];
        $this->gender = $data[4];
        // dd($this->path);
        $this->openModal();
    }

    public function delete($id)
    {
    	if (is_null($id)) {
    		dd('data not found')
    	}
        $bio = Biodata::find($id); 
    	// dd(storage_path($bio->path));
        @Storage::delete($bio->path);
    	@Storage::delete('files/'.Biodata::find($id)->nama_file.'.txt');
        // @File::delete('app/files'.Biodata::find(storage_path($this->data_id)->nama_file).'.txt');
        $bio->delete(); 
        session()->flash('message', $bio->nama_file . ' Deleted'); 
    }
}
