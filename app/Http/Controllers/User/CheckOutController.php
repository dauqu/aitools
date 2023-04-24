<?php

namespace App\Http\Controllers\User;

use Carbon\Carbon;
use App\Models\Plan;
use App\Models\User;
use App\Models\Config;
use App\Models\Medias;
use App\Models\Gateway;
use App\Models\Setting;
use App\Models\Currency;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\AiImages;
use App\Models\Generate;
use Illuminate\Support\Facades\Auth;

class CheckOutController extends Controller
{
    // Checkout
    public function checkout(Request $request, $id)
    {
        // Choosed plan
        $selected_plan = Plan::where('id', $id)->where('status', 1)->first();

        // Check selected plan status
        if ($selected_plan == null) {
            return redirect()->route('user.plans')->with('failed', trans('Your current plan is not available. Choose another plan.'));
        } else {

            // Queries
            $config = Config::get();

            // Check plan status
            if ($selected_plan == null) {
                return view('errors.404');
            } else {
                // Get user used word count
                $usesWordsCount = Generate::where('generate_by', Auth::user()->id)->sum('word_count');
                // Get user used images count
                $usesImagesCount = AiImages::where('generate_by', Auth::user()->id)->count();

                // Check word count
                if ($usesWordsCount < (int)$selected_plan->max_words && $usesImagesCount < (int)$selected_plan->max_images) {
                    // Selected plan price
                    if ($selected_plan->price == 0) {
                        if (Auth::user()->billing_name == "") {
                            return redirect()->route('user.billing', $id);
                        } else {
                            // Invoice generate by user
                            $invoice_details = [];

                            $invoice_details['from_billing_name'] = $config[16]->config_value;
                            $invoice_details['from_billing_address'] = $config[19]->config_value;
                            $invoice_details['from_billing_city'] = $config[20]->config_value;
                            $invoice_details['from_billing_state'] = $config[21]->config_value;
                            $invoice_details['from_billing_zipcode'] = $config[22]->config_value;
                            $invoice_details['from_billing_country'] = $config[23]->config_value;
                            $invoice_details['from_vat_number'] = $config[26]->config_value;
                            $invoice_details['from_billing_phone'] = $config[18]->config_value;
                            $invoice_details['from_billing_email'] = $config[17]->config_value;
                            $invoice_details['to_billing_name'] = $request->billing_name;
                            $invoice_details['to_billing_address'] = $request->billing_address;
                            $invoice_details['to_billing_city'] = $request->billing_city;
                            $invoice_details['to_billing_state'] = $request->billing_state;
                            $invoice_details['to_billing_zipcode'] = $request->billing_zipcode;
                            $invoice_details['to_billing_country'] = $request->billing_country;
                            $invoice_details['to_billing_phone'] = $request->billing_phone;
                            $invoice_details['to_billing_email'] = $request->billing_email;
                            $invoice_details['to_vat_number'] = $request->vat_number;
                            $invoice_details['tax_name'] = $config[24]->config_value;
                            $invoice_details['tax_type'] = $config[14]->config_value;
                            $invoice_details['tax_value'] = $config[25]->config_value;
                            $invoice_details['invoice_amount'] = 0;
                            $invoice_details['subtotal'] = 0;
                            $invoice_details['tax_amount'] = 0;

                            // Save transaction
                            $transaction = new Transaction();
                            $transaction->transaction_date = now();
                            $transaction->transaction_id = uniqid();
                            $transaction->user_id = Auth::user()->id;
                            $transaction->plan_id = $selected_plan->id;
                            $transaction->desciption = $selected_plan->name . " Plan";
                            $transaction->payment_gateway_name = "FREE";
                            $transaction->transaction_amount = $selected_plan->price;
                            $transaction->transaction_currency = $config[1]->config_value;
                            $transaction->invoice_details = json_encode($invoice_details);
                            $transaction->payment_status = "SUCCESS";
                            $transaction->save();

                            // Set validity
                            $plan_validity = Carbon::now();
                            $plan_validity->addDays($selected_plan->validity);

                            // Update validity by user
                            User::where('id', Auth::user()->id)->update([
                                'plan_id' => $id,
                                'term' => "9999",
                                'plan_validity' => $plan_validity,
                                'plan_activation_date' => now(),
                                'plan_details' => $selected_plan,
                                'api_key' => "",
                            ]);

                            return redirect()->route('user.plans')->with('success', trans("FREE Plan activated!"));
                        }
                    } else {
                        // Queries
                        $settings = Setting::where('status', 1)->first();
                        $config = Config::get();
                        $currency = Currency::where('iso_code', $config[1]->config_value)->first();
                        $gateways = Gateway::where('is_status', 'enabled')->where('status', 1)->get();

                        // Current plan price
                        $price = $selected_plan->price;
                        $tax = $config[25]->config_value;

                        // Calculate total
                        $total = ((int)($price) * (float)($tax) / 100) + (int)($price);

                        return view('user.pages.checkout.index', compact('settings', 'config', 'currency', 'selected_plan', 'gateways', 'total'));
                    }
                } else {
                    // Redirect
                    return redirect()->route('user.plans')->with('failed', trans('You cannot choose this plan as the words / images count of the plan you have chosen is less than the count of the documents / images you have already stored. So, please choose a different plan.'));
                }
            }
        }
    }
}
