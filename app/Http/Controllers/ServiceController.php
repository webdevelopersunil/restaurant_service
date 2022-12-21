<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Service;
use App\Models\Booking;
use App\Models\User;
use App\Models\BookingRating;
use App\Models\CouponServiceMapping;
use App\Models\ProviderServiceAddressMapping;
use App\DataTables\ServiceDataTable;
use Illuminate\Support\Facades\Storage;
use App\Models\Media;

class ServiceController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ServiceDataTable $dataTable)
    {
        $pageTitle = __('messages.list_form_title',['form' => __('messages.service')] );
        $auth_user = authSession();
        $assets = ['datatable'];
        return $dataTable->render('service.index', compact('pageTitle','auth_user','assets'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        $id = $request->id;
        $auth_user = authSession();

        $servicedata = Service::find($id);
        $pageTitle = __('messages.update_form_title',['form'=> __('messages.service')]);
        
        if($servicedata == null){
            $pageTitle = __('messages.add_button_form',['form' => __('messages.service')]);
            $servicedata = new Service;
        }
        
        return view('service.create', compact('pageTitle' ,'servicedata' ,'auth_user' ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
       
        $services = $request->all();

        $data['path'] = 'services';
         if($request->hasFile('logo')) {
            $file = $request->file('logo');
            $logo_image =  $file[0]->getClientOriginalName();
            $file_size =  $file[0]->getSize();
            $mime_type =  $file[0]->getMimeType();
            $services['logo'] = $logo_image;
        }


       
        $result = Service::updateOrCreate(['id' => $request->id], $services);

        // save logo file in directory
        if($request->hasFile('logo')) {
            if($request->id ){
                $media_record = Media::where('model_id', $request->id)->first();
                if($media_record){
                    $old_media_id = $media_record->id;
                    // remove old media image and record from db
                    remove_old_media_file($old_media_id, 'service_attachment');
                }
                $res = storeMediaFile($result, $file, 'service_attachment');
            }else{
                $res = storeMediaFile($result, $file, 'service_attachment');
            }
            
        }

        $message = __('messages.update_form',[ 'form' => __('messages.service') ] );
		if($result->wasRecentlyCreated){
			$message = __('messages.save_form',[ 'form' => __('messages.service') ] );
		}

        if($request->is('api/*')) {
            return comman_message_response($message);
		}

		return redirect(route('service.index'))->withSuccess($message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        if(demoUserPermission()){
            if(request()->is('api/*')){
                return comman_message_response( __('messages.demo_permission_denied') );
            }
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $service = Service::find($id);
        $msg= __('messages.msg_fail_to_delete',['item' => __('messages.service')] );
        
        if($service!='') {
            $service->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.service')] );
        }
        if(request()->is('api/*')){
            return comman_custom_response(['message'=> $msg , 'status' => true]);
        }
        return redirect()->back()->withSuccess($msg);
    }
    public function action(Request $request){
        $id = $request->id;
        $service = Service::withTrashed()->where('id',$id)->first();
        $msg = __('messages.not_found_entry',['name' => __('messages.service')] );
        if($request->type === 'restore'){
            $service->restore();
            $msg = __('messages.msg_restored',['name' => __('messages.service')] );
        }

        if($request->type === 'forcedelete'){
            $service->forceDelete();
            $msg = __('messages.msg_forcedelete',['name' => __('messages.service')] );
        }

        return comman_custom_response(['message'=> $msg , 'status' => true]);
    }
}
