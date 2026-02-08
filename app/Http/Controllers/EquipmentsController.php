<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Equipments;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Redirect;
use Illuminate\View\View;



class EquipmentsController extends Controller
{
    //
            //List all Pateints
            public function index(){
                $equipments = Equipments::orderBy('created_at', 'ASC')->get();
                return view('admin.equipments.index', compact('equipments'));
            }
            
                /**
                 * Show the form for creating a new patient.
                 */
               
        public function create()
            {
             
                // Pass the next serial number to the view
                return view('admin.equipments.register_equipments');
            }
                /**
                 * Store a newly created patient in storage.
                 */
                public function store(Request $request)
                {
                    Equipments::create($request->all());
             
                    return redirect()->route('equipments.create')->with('success', 'Equipment Added Successfully');
                }
            
            /**
                 * Display the specified resource.
                 */
               
              /**
                 * Show the form for editing the specified resource.
                 */
                public function edit(string $id)
                {
                    $equipment = Equipments::findOrFail($id);
              
                    return view('admin.equipments.edit', compact('equipment'));
                }
            
                /**
                 * Update the specified resource in storage.
                 */
                public function update(Request $request, string $id)
                {
                    $equipment = Equipments::findOrFail($id);
              
                    $equipment->update($request->all());
              
                    return redirect()->route('equipments')->with('success', 'Equipment Updated Successfully');
                }
            
                /**
                 * Remove the specified resource from storage.
                 */
                public function destroy(string $id)
                {
                    $equipment = $equipments::findOrFail($id);
              
                    $equipment->delete();
              
                    return redirect()->route('equipments')->with('success', 'Equipment Deleted Successfully');
                }
}
