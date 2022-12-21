<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\User;
use App\Models\Provider;
use App\Models\Booking;
use App\Models\Service;
use App\Models\ProviderDocument;
use App\DataTables\ProviderDataTable;
use App\DataTables\ServiceDataTable;
use App\Http\Requests\UserRequest;
use App\Mail\mailToProvider;
use Mail;
use App\Http\Service\PushNotificationService;


class ProviderController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(ProviderDataTable $dataTable, Request $request)
    {
        $pageTitle = __('messages.list_form_title',['form' => __('messages.provider')] );
        if(!empty($request->status)){
            $pageTitle = __('messages.pending_list_form_title',['form' => __('messages.provider')] );
        }
        $auth_user = authSession();
        $assets = ['datatable'];
        return $dataTable
                ->with('list_status',$request->status)
                ->render('provider.index', compact('pageTitle','auth_user','assets'));
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

        $providerdata = User::find($id);
        $pageTitle = __('messages.update_form_title',['form'=> __('messages.provider')]);
        
        if($providerdata == null){
            $pageTitle = __('messages.add_button_form',['form' => __('messages.provider')]);
            $providerdata = new User;
        }
        
        return view('provider.create', compact('pageTitle' ,'providerdata' ,'auth_user' ));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(UserRequest $request)
    {
        $loginuser = \Auth::user();
        if(demoUserPermission()){
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $data = $request->all();
        $id = $data['id'];
        $data['user_type'] = $data['user_type'] ?? 'provider';
        $data['is_featured'] = 0;
        
        if($request->has('is_featured')){
			$data['is_featured'] = 1;
		}

        $data['display_name'] = $data['first_name']." ".$data['last_name'];
        // Save User data...
        if($id == null){
            $data['password'] = bcrypt($data['password']);
            $user = User::create($data);
        }else{
            $user = User::findOrFail($id);
            // User data...
            // $user->removeRole($user->user_type);
            $user->fill($data)->update();
        }
        if($data['status'] == 1 && auth()->user()->hasAnyRole(['admin'])){
            try {
                \Mail::send('verification.verification_email',
                array(), function($message) use ($user)
                {
                    $message->from(env('MAIL_FROM_ADDRESS'));
                    $message->to($user->email);
                });
            } catch (\Throwable $th) {
                //throw $th;
            }
           
        }
        $user->assignRole($data['user_type']);
        storeMediaFile($user,$request->profile_image, 'profile_image');
        $message = __('messages.update_form',[ 'form' => __('messages.provider') ] );
		if($user->wasRecentlyCreated){
			$message = __('messages.save_form',[ 'form' => __('messages.provider') ] );
		}
        if($user->providerTaxMapping()->count() > 0)
        {
            $user->providerTaxMapping()->delete();
        }
        if($request->tax_id != null) {
            foreach($request->tax_id as $tax) {
                $provider_tax = [
                    'provider_id'   => $user->id,
                    'tax_id'   => $tax,
                ];
                $user->providerTaxMapping()->insert($provider_tax);
            }
        }

        if($request->is('api/*')) {
            return comman_message_response($message);
		}

		return redirect(route('provider.index'))->withSuccess($message);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ServiceDataTable $dataTable,$id)
    {
        $auth_user = authSession();
        $providerdata = Provider::with('user', 'providerPatymentMethod', 'states', 'documents')->where('id',$id)->first();
        $profile_picture = "";
        $profile_picture_data  = $providerdata->documents->where('document_type', 'provider_profile_picture')->first();
        
        if($profile_picture_data){
            $profile_picture = $profile_picture_data->document->name;
        }
        
        if(empty($providerdata))
        {
            $msg = __('messages.not_found_entry',['name' => __('messages.provider')] );
            return redirect(route('provider.index'))->withError($msg);
        }
        $pageTitle = __('messages.view_form_title',['form'=> __('messages.provider')]);

        return $dataTable
        ->with('provider_id',$id)
        ->render('provider.view', compact('pageTitle' ,'providerdata' ,'auth_user', 'profile_picture' ));
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
            return  redirect()->back()->withErrors(trans('messages.demo_permission_denied'));
        }
        $provider = User::find($id);
        $msg= __('messages.msg_fail_to_delete',['name' => __('messages.provider')] );
        
        if($provider != '') { 
            $provider->delete();
            $msg= __('messages.msg_deleted',['name' => __('messages.provider')] );
        }

        return redirect()->back()->withSuccess($msg);
    }
    public function action(Request $request){
        $id = $request->id;

        $provider  = Provider::where('id',$id)->first();
        // $provider  = User::withTrashed()->where('id',$id)->first();
        $msg = __('messages.not_found_entry',['name' => __('messages.provider')] );
        if($request->type == 'restore') {
            $provider->restore();
            $msg = __('messages.msg_restored',['name' => __('messages.provider')] );
        }

        if($request->type === 'forcedelete'){
            $provider->forceDelete();
            $msg = __('messages.msg_forcedelete',['name' => __('messages.provider')] );
        }
        
        return comman_custom_response(['message'=> $msg , 'status' => true]);
    }

    /**
     * change provider status
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function changeProviderStatus($id, $status)
    {
        $user = Auth::user();
        if($status === "approve"){
            $providerStatus  = 'approved';
        }elseif($status === "unapprove"){
            $providerStatus  = 'suspended';
        }

        $provider = Provider::with('user')->where('id', $id)->first();

        $updateStatus = $provider->update(['status'=> $providerStatus]);   

        $theUpdatedData = $provider->refresh(); 
        
        $data = [
            'first_name' => $theUpdatedData->user->first_name,
            'last_name' => $theUpdatedData->user->last_name,
            'toEmail' => $theUpdatedData->user->email,
            'status' => $theUpdatedData->status,
        ];

        $emailTemplate = get_email_template('approved_decline_provider');

        if($data && $emailTemplate){
            Mail::to($theUpdatedData->user->email)->send(new mailToProvider($data));

            $notification_data = [
                'title' => 'Account '. $theUpdatedData->status,
                'type' => 'email',
                'message' => "Admin has ".$theUpdatedData->status." the account.",
                "ios_badgeType"=>"Increase",
                "ios_badgeCount"=> 1
            ];

            (new PushNotificationService)->sendNotificationFCM($user,$notification_data);
        }

       

        if($updateStatus){
           
            return redirect('admin/provider');
        }
    }
}
