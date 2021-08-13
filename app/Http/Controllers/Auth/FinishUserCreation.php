<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Credential;
use App\Models\CredentialLicenseTypes;
use App\Models\Department;
use App\Models\ProfessionalCredentialFilters;
use App\Models\ProfessionalRole;
use App\Models\States;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Providers\RouteServiceProvider;

class FinishUserCreation extends Controller
{
    public function show(Request $request)
    {
        $credentials = Credential::all();
        $roles = ProfessionalRole::all();
        $filters = ProfessionalCredentialFilters::all();
        $licenseTypes = CredentialLicenseTypes::all();
        $states = States::all();
        $departments = [];

        if($request->session()->get('Organization'))
        {
            $departments = Department::where('OrganizationID',$request->session()->get('Organization'))->get();
            
        }
        return Inertia::render('Auth/FinishUser', [
            'departments'=>$departments,
            'credentials'=>$credentials,
            'roles'=>$roles,
            'credentialFilter'=>$filters,
            'licenseTypes'=>$licenseTypes,
            'states'=>$states
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'departmentID'=>'numeric',
            'professionalRoleID'=>'required|numeric|gt:0',
            'credentialID'=>'required|numeric|gt:0'
        ]);
        $user = Auth::user();
        if($request->departmentID > 0)
        {
            $user->DepartmentID = $request->departmentID;
        }

        $user->CredentialID = $request->credentialID;

        if($request->professionalRoleID == 3)
        {
            
            $request->validate([
                'EMSData.stateID'=>'numeric|required|gt:0',
                'EMSData.licenseNo'=>'required',
                'EMSData.stateExpDate'=>'required|date',
                'EMSData.NREMT'=>'required',
                'EMSData.reregDate'=>'required|date',
                'EMSData.licenseType'=>'required|numeric|gt:0'
            ]);

            // dd($request->EMSData->stateID);
            
            $user->StateOfLicensureID = $request->EMSData['stateID'];
            $user->StateLicenseNumber = $request->EMSData['licenseNo'];
            $user->StateLicenseExpirationDate = date('Y-m-d H:i:s',strtotime($request->EMSData['stateExpDate']));
            $user->NEMSID = $request->EMSData['NEMSID'];
            $user->NREMTCertificationNumber = $request->EMSData['NREMT'];
            $user->NREMTReregistrationDate = date('Y-m-d H:i:s',strtotime($request->EMSData['reregDate']));
            $user->CredentialLicenseTypeID = $request->EMSData['licenseType'];
        }
        
        $user->save();

        return redirect(RouteServiceProvider::HOME);

    }
}
