<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerRequest;
use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(): View
    {
        return view('accounts.index', ['kind' => 'customers', 'records' => Customer::orderBy('full_name')->get()]);
    }

    public function create(): View
    {
        return view('accounts.form', ['kind' => 'customers', 'record' => new Customer]);
    }

    public function store(CustomerRequest $request): RedirectResponse
    {
        Customer::create($request->validated());

        return to_route('customers.index')->with('success', 'Customer created.');
    }

    public function edit(Customer $customer): View
    {
        return view('accounts.form', ['kind' => 'customers', 'record' => $customer]);
    }

    public function update(CustomerRequest $request, Customer $customer): RedirectResponse
    {
        $customer->update($request->validated());

        return to_route('customers.index')->with('success', 'Customer updated.');
    }
}
