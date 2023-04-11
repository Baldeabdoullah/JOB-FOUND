<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Listing;
use Illuminate\Validation\Rule;

class ListingController extends Controller
{
    //show all listings
    public function index()
    {


        return view('listings.index',   [

            'listings' => Listing::latest()->filter(request(['tag', 'search']))->paginate(6)
        ]);
    }

    // show single listin

    public function show(Listing $listing)
    {
        return view('listings.show', [
            'listing' => $listing
        ]);
    }

    // show create form

    public function create()
    {
        return view('listings.create');
    }

    //Store lIstings data

    public function store(Request  $request)
    {
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required', Rule::unique('listings', 'company')],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }


        $formFields['user_id'] = auth()->id();

        Listing::create($formFields);


        return redirect('/')->with('message', 'Listing Created successfully');
    }
    //show Edit Form

    public function edit(Listing $listing)
    {
        if ($listing->user_id != auth()->id()) {
            abort(403, "Non autorisé: Vous n'êtes pas propriétaire de ce post ");
        }
        return view('listings.edit', ['listing' => $listing]);
    }

    // update Listing data
    public function update(Request  $request, Listing $listing)
    {

        // s'assurer que l'utilisateur connecté est le propriétaire du post
        if ($listing->user_id != auth()->id()) {
            abort(403, "Non autorisé: Vous n'êtes pas propriétaire de ce post ");
        }
        $formFields = $request->validate([
            'title' => 'required',
            'company' => ['required'],
            'location' => 'required',
            'website' => 'required',
            'email' => ['required', 'email'],
            'tags' => 'required',
            'description' => 'required'
        ]);

        if ($request->hasFile('logo')) {
            $formFields['logo'] = $request->file('logo')->store('logos', 'public');
        }

        $listing->update($formFields);


        return back()->with('message', 'Listing Updated successfully');
    }

    // Delete Listing

    public function destroy(Listing $listing)
    {
        if ($listing->user_id != auth()->id()) {
            abort(403, "Non autorisé: Vous n'êtes pas propriétaire de ce post ");
        }
        $listing->delete();
        return redirect('/')->with('message', 'Listing deleted Successfully');
    }

    // Manage Listings
    public function manage()
    {
        return view('listings.manage', ['listings' => auth()->user()->listings()->get()]);
    }
}
