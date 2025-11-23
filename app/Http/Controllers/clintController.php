<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\clientCreation;


class clintController extends Controller
{
    public function clientCreation(){
        $allClient  = clientCreation::all();
        return view('client.clientCreation',[
            'allClient' => $allClient,
        ]);
    }

     public function saveClient(Request $requ){
        if(empty($requ->itemId)):
            $data   = new clientCreation();
        else:
            $data   = clientCreation::find($requ->itemId);
        endif;

        $data->client_name       = $requ->fullName;
        $data->client_email     = $requ->email;
        $data->client_phone       = $requ->mobileNo;
        $data->client_acNum     = $requ->acNumber;
        $data->client_opBalance = $requ->clientOpBalance;
        $data->client_regDate     = $requ->registerDate;
        if($data->save()):
            return back()->with('success','Success! Account creation successfully');
        else:
            return back()->with('error','Opps! Account creation failed. Please try later');
        endif;
        
    }

    public function clientEdit($id){
        return view('client.clientCreation',[
            'itemId' => $id,
        ]);
    }

    public function updateClient(Request $requ){
        $data   = clientCreation::find($requ->itemId);

        $data->client_name       = $requ->fullName;
        $data->client_email     = $requ->email;
        $data->client_phone       = $requ->mobileNo;
        $data->client_acNum     = $requ->acNumber;
        $data->client_opBalance = $requ->clientOpBalance;
        $data->client_regDate     = $requ->registerDate;
        if($data->save()):
            return redirect(route('clientCreation'))->with('success','Success! Account update successfully');
        else:
            return back()->with('error','Opps! Account update failed. Please try later');
        endif;
        
    }

    public function deleteClient($id){
        $data   = clientCreation::find($id);
        if($data->delete()):
            return back()->with('success','Success! Client deleted successfully');
        else:
            return back()->with('error','Opps! Client deletion failed. Please try later');
        endif;
        
    }

}
