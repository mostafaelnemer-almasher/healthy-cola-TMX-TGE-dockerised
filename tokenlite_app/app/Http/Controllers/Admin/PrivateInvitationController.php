<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PrivateInvitation;
use Illuminate\Http\Request;
use App\Models\Setting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class PrivateInvitationController extends Controller
{

  /**
   * Display the private sale invite codes list.
   *
   * @param Request $request
   * @param  String $string
   * @return \Illuminate\Http\Response
   */
  public function index(Request $request)
  {
    $privateInvites = PrivateInvitation::orderBy('created_at', 'desc')->get();
    return view('admin.private-sale-invite', compact('privateInvites'));
  }


  /**
   * Updating data depending on types.
   * 
   * @param Request $request
   * @return \Illuminate\Http\Response
   */
  public function update(Request $request)
  {
    $type = $request->input('req_type');

    $ret['msg'] = 'info';
    $ret['message'] = __('messages.nothing');


    // Create and update private sale invite code
    if ($type == 'private_invitation_create' || $type == 'private_invitation_update' ) {
      $validator = Validator::make($request->all(), [
        'invitation_label' => 'required|string|max:25',
        'invitation_code' => "nullable|string|min:4|max:20|unique:private_invitations,code,$request->id",
        'invitation_start' => 'nullable|date',
        'invitation_end' => [
          'nullable',
          'date',
            function ($attribute, $value, $fail) {
              $invitationStart = request()->input('invitation_start');
              if ($invitationStart && $value && $value < $invitationStart) {
                  $fail('The end date must be a date after or equal to start date.');
              }
            },
          ],
      ], [
        'invitation_label.required' => __('Label is required'),
        'invitation_label.string' => __('Label must be string'),
        'invitation_label.max' => __('Label may not be greater than 25 chracter'),

        'invitation_code.string' => __('Code must be string'),
        'invitation_code.min' => __('Code can not be less than 4 chracter'),
        'invitation_code.max' => __('Code can not be greater than 20 chracter'),
        'invitation_code.unique' => __('Code has already been taken'),

        'invitation_start.date' => __('Start date must be a date'),
        'invitation_end.date' => __('End date must be a date'),
        'invitation_end.after_or_equal' => __('The end date must be a date after start date')
      ]);

      if ($validator->fails()) {
        $msg = '';
        if ($validator->errors()->hasAny(['invitation_label', 'invitation_code', 'invitation_start', 'invitation_end'])) {
            $msg = $validator->errors()->first();
        } else {
            $msg = __('messages.form.wrong');
        }

        $ret['msg'] = 'warning';
        $ret['message'] = $msg;
      } else {
        $code = (isset($request->invitation_code)) ? (string) $request->invitation_code : Str::random(8);
        $startDate = (isset($request->invitation_start)) ? Carbon::create($request->invitation_start) : null;

        $endDate = (isset($request->invitation_end)) ? Carbon::create($request->invitation_end)->setTime(23,59,59) : null;
        $invitation = PrivateInvitation::updateOrCreate([
          'id' => $request->id
        ],[
          'label' => $request->invitation_label,
          'code' => $code,
          'start_date' => $startDate,
          'end_date' => $endDate,
          'status' => ($type == 'private_invitation_create') ? 'active' : (($type == 'private_invitation_update' && $request->status) ? 'active' :'inactive'),
        ]);

        $sucess_msg = ($type == 'private_invitation_update') ? __('messages.update.success', ['what' => 'Inviation']) :
                __('messages.create.success', ['what' => 'Inviation']);
        
        $failed_msg = ($type == 'private_invitation_update') ? __('messages.update.failed', ['what' => 'Inviation']) :
                __('messages.create.failed', ['what' => 'Inviation']);

        if($invitation) {
          $ret['msg'] = 'success';
          $ret['message'] = $sucess_msg;
          $ret['link'] = route('admin.private.invite.list');
        } else {
          $ret['msg'] = 'warning';
          $ret['message'] = $failed_msg;    
        }
      }
    }

    // Update private sale invite setting
    if ($type == 'inv_settings_update') {
      $validator = Validator::make($request->all(),[
        'private_invitation_active' => 'sometimes|required',
        'registration_option' => 'sometimes|required',
        'disable_reg_msg' => 'nullable|string',
        'pinv_alt' => 'sometimes|required',
        'pinv_alt_msg' => 'nullable|string',
      ]);

      if ($validator->fails()) {
        $msg = '';
        if ($validator->errors()->hasAny(['private_invitation_active', 'registration_option', 'disable_reg_msg', 'pinv_alt', 'pinv_alt_msg'])) {
            $msg = $validator->errors()->first();
        } else {
            $msg = __('messages.form.wrong');
        }

        $ret['msg'] = 'warning';
        $ret['message'] = $msg;
      } else {
        Setting::updateValue('private_invitation_active', isset($request->private_invitation_active) ? 1 : 0);
        Setting::updateValue('registration_option', $request->registration_option);
        Setting::updateValue('disable_reg_msg', $request->disable_reg_msg);
        Setting::updateValue('pinv_alt', isset($request->pinv_alt) ? 1 : 0);
        Setting::updateValue('pinv_alt_msg', $request->pinv_alt_msg);

        $ret['msg'] = 'success';
        $ret['message'] = __('messages.update.success', ['what' => 'Invitation Settings']);
      }
    }

    // Update private sale invite status 
    if ($type == 'private_inv_'.$request->req_status) {
      $id = $request->id;
      $req_status = $request->req_status;

      if ($id !== null) {
        $privateInvite = PrivateInvitation::find($id);

        if($req_status != 'delete') {
          $privateInvite->status = $req_status;
          $privateInvite->save();

          $ret['status'] = $privateInvite->status;
          $ret['msg'] = 'success';
          $ret['message'] = ($privateInvite->status == 'active') ? __('messages.active.success', ['what' => 'Invitation']) : __('messages.inactive.success', ['what' => 'Invitation']);
        } else if($req_status == 'delete') {
          $privateInvite->delete();

          $ret['status'] = 'delete';
          $ret['msg'] = 'success';
          $ret['message'] = __('messages.delete.delete', ['what' => 'Invitation']);
        } else {
          $ret['msg'] = 'error';
          $ret['message'] =  ($req_status == 'active') ? __('messages.active.failed', ['what' => 'Invitation']) : (($req_status == 'inactive')? __('messages.inactive.failed', ['what' => 'Invitation']): __('messages.delete.delete_failed', ['what' => 'Invitation']));
        }
      }
    }

    if ($request->ajax()) {
        return response()->json($ret);
    }
    return back()->with([$ret['msg'] => $ret['message']]);
  }

  /**
   * Return to modal view.
   * @return void
   */
  public function modal_show(Request $request)
  {
    $req_type = $request->input('req_type');
    $form_type = $request->input('form_type');
    $privateInvite = null;

    if($req_type == 'private_'.$form_type) {
      if($request->filled('id')) {
        $privateInvite = PrivateInvitation::find($request->id);
      }

      return view('modals.'.$form_type, compact('privateInvite'))->render();
    }
  }
}

