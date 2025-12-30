<?php

namespace App\Http\Controllers\backend;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreContactRequest;
use App\Http\Requests\UpdateContactRequest;
use Illuminate\Http\Request;
use App\Models\Contact;
use Illuminate\Support\Facades\Auth;

class ContactController extends Controller
{
    public function index()
    {
        $contacts = contact::orderBy('created_at','DESC')->select()->paginate(4);
        return view('backend.contact.index',compact('contacts'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
       $contacts = Contact::orderBy('sort_order','ASC') ->get();
        return view('backend.banner.index',data: compact('contacts'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreContactRequest $request)
    {
        $contact = new Contact();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->phone = $request->phone;
        $contact->title = $request->title;
        $contact->content = $request-> content;
        $contact->reply_id = $request->reply_id;
        $contact->user_id = $request->user_id;
        $contact->timeline = $request->timeline;
        $contact->created_by = Auth::id()?? 1;
        $contact->created_at = date('Y-m-d H:i:s');
        $contact->status = $request->status;
        $contact->save();
        return redirect()->route('admin.contact.index')->with('success','Thêm thành công');   
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $contact = Contact::where('id',$id)->first();
        if($contact == null){
            return redirect()->back()->with('error','Không tồn tại ');
        }
        return view('backend.contact.show',compact('contact'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $contact = Contact::where('id',$id)->first();
        return view('backend.contact.edit',compact('contact'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateContactRequest $request, string $id)
    {
        $contact = Contact::where('id',$id)->first();
        $contact->name = $request->name;
        $contact->email = $request->email;
        $contact->phone = $request->phone;
        $contact->title = $request->title;
        $contact->content = $request-> content;
        $contact->reply_id =Auth::id()?? 1;
        $contact->user_id = Auth::id()?? 1;
        $contact->timeline = $request->timeline;
        $contact->created_by = Auth::id()?? 1;
        $contact->created_at = date('Y-m-d H:i:s');
        $contact->status = $request->status;
        if($contact->save()){
            return redirect()->route('admin.contact.index')->with('success','Contact đã được cập nhật');
            
        }
        return redirect()->back()->with('error','Lỗi thêm');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //xoa sp khoi csdl
        $contact =Contact::withTrashed()->where('id',$id)->first();
        if($contact != null){           
            $contact->forceDelete();
            return redirect()->route("admin.contact.trash")->with('success','Xóa thành công');
        }
        return redirect()->route("admin.contact.trash")->with('error','Mẫu tin không tồn tại');
    }
    //danhsachsanphamrac
    public function trash()
    {
        $contacts = Contact::onlyTrashed() ->orderBy('created_at','DESC') ->get();
        return view('backend.contact.trash',compact('contacts'));
    }   
    public function status(string $id)
    {
        //xulythaydoitrangthai
        $contact = Contact::find($id);
        if ($contact != null) {
            $contact->status = ($contact->status == 1) ? 2 : 1;
            $contact->save();

            return redirect()->route('admin.contact.index')->with('success', 'Trạng thái đã được thay đổi');
        }
        return redirect()->route('admin.contact.index')->with('error', 'Mẫu tin không tồn tại');
    }
    public function delete(string $id)
    {
        //xulyxoavaothungrac
        $contact = Contact::find($id);
        if($contact != null){
            $contact->delete();
            return redirect()->route("admin.contact.index")->with('success','Xóa vào thùng rác thành công');
        }
        return redirect()->route("admin.contact.index")->with('error','Mẫu tin không tồn tại');
    }
    public function restore(string $id)
    {
        //xulykhoiphuckhoithungrac
        $contact = Contact::withTrashed()->where('id',$id);
        if($contact->first() != null){
            $contact->restore();
            return redirect()->route("admin.contact.trash")->with('success','Khôi phục thành công');
        }
        return redirect()->route("admin.contact.trash")->with('error','Mẫu này không tồn tại');
    }
}
