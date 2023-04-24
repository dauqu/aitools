<?php

namespace App\Http\Controllers\User;

use App\Models\Plan;
use App\Models\User;
use App\Models\Config;
use App\Models\Setting;
use App\Models\Generate;
use Illuminate\Http\Request;
use App\Classes\AiSpeechText;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class AiSpeechToTextController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */

    // Show all AI Speech To Text
    public function indexAllAiSpeechToText()
    {
        // Check active plans
        $active_plan = Plan::where('id', Auth::user()->plan_id)->first();

        // Check user plan
        $plan = User::where('id', Auth::user()->id)->first();

        // Check plan
        if ($active_plan != null) {
            // Get User Generated Speech To Text
            $speechToTexts = Generate::where('generate_by', Auth::user()->id)->where('type', 'Speech to Text')->where('status', 1)->orderBy('updated_at', 'desc')->paginate(7);
            $settings = Setting::where('status', 1)->first();

            return view('user.pages.ai-speech.index', compact('speechToTexts', 'settings'));
        } else {
            // Page redirect in plan is not activated
            return redirect()->route('user.plans');
        }
    }

    // Show all AI Speech To Text
    public function indexNewAiSpeechToText()
    {
        // Queries
        $config = Config::get();

        // Check active plans
        $active_plan = Plan::where('id', Auth::user()->plan_id)->first();

        // Check plan
        if ($active_plan != null) {

            // Get user used Speech To Text
            $speechToTextCount = Generate::where('generate_by', Auth::user()->id)->sum('word_count');

            // Get plan details
            $plan = User::where('id', Auth::user()->id)->first();
            $plan_details = json_decode($plan->plan_details);

            // Check maximum words
            $max_words = $plan_details->max_words;

            // Check user used Speech To Text count
            if ($speechToTextCount < (int)$max_words) {
                // Check plan
                if ($active_plan != null) {
                    return view('user.pages.ai-speech.pages.index', compact('config', 'active_plan'));
                } else {
                    // Page redirect in plan is not activated
                    return redirect()->route('user.plans');
                }
            } else {
                // Redirect
                return redirect()->route('user.all.ai.speech.to.text')->with('failed', trans('Maximum speech to text limit is exceeded, Please upgrade your plan.'));
            }
        } else {
            // Page redirect in plan is not activated
            return redirect()->route('user.plans');
        }
    }

    // Generate Speech To Text
    public function generateAiSpeechToText(Request $request)
    {
        // Get user used word count
        $usesWordsCount = Generate::where('generate_by', Auth::user()->id)->sum('word_count');

        // Get plan details
        $plan = User::where('id', Auth::user()->id)->first();
        $plan_details = json_decode($plan->plan_details);

        // Check maximum words
        $max_words = $plan_details->max_words;

        if ($usesWordsCount < (int)$max_words) {
            // Generate AI Content
            $tool = new AiSpeechText;
            $tool->generate($request);

            // Check result
            if ($tool->result != null) {
                // Result
                $generateId = random_int(000000000000001, 99999999999999);

                // Parameters
                $audio = $request->file('audio');
                $audioName = $audio->getClientOriginalName();

                // Save Generate Content
                $saveContent = new Generate();
                $saveContent->generate_id = $generateId;
                $saveContent->generate_by = Auth::user()->id;
                $saveContent->name = $audioName;
                $saveContent->type = 'Speech to Text';
                $saveContent->lang = "en";
                $saveContent->content = $tool->result;
                $saveContent->word_count = $this->utf8_str_word_count($tool->result);
                $saveContent->parameters = "";
                $saveContent->save();

                return response()->json([$tool->result, $generateId], 200);
            } else {
                return response()->json($tool->result, 200);
            }
        } else {
            // Redirect
            return response()->json(404);
        }
    } 

    // View generate content
    public function viewAiSpeechToText($generateId)
    {
        // Get single content data
        $content = Generate::where('generate_id', $generateId)->where('generate_by', Auth::user()->id)->first();

        // Check content
        if (isset($content)) {
            return view('user.pages.ai-speech.view', compact('content'));
        } else {
            return redirect()->route('user.all.ai.speech.to.text')->with('failed', trans('Wrong content ID.'));
        }
    }

    // Edit generate content
    public function editAiSpeechToText($generateId)
    {
        // Get single content data
        $content = Generate::where('generate_id', $generateId)->where('generate_by', Auth::user()->id)->first();
        $config = Config::get();

        // Check content
        if (isset($content)) {
            return view('user.pages.ai-speech.edit', compact('content', 'config'));
        } else {
            return redirect()->route('user.all.ai.speech.to.text')->with('failed', trans('Wrong content ID.'));
        }
    }

    // Update generate content
    public function updateAiSpeechToText(Request $request)
    {
        // Update single content data
        Generate::where('generate_id', $request->generateId)->where('generate_by', Auth::user()->id)->update([
            'content' => $request->result
        ]);

        return redirect()->route('user.edit.ai.speech.to.text', $request->generateId)->with('success', trans('Updated!'));
    }

    // Export docs
    public function exportDocsAiSpeechToText($generateId)
    {
        // Get single content data
        $content = Generate::where('generate_id', $generateId)->where('generate_by', Auth::user()->id)->first();

        $headers = array(
            'Content-type' => 'text/html',
            'Content-Disposition' => 'attachment; Filename=' . $content->name . '.doc'
        );

        return response()->make(view('user.pages.ai-speech.includes.export-doc', compact('content')), 200, $headers);
    }


    // Word count
    function utf8_str_word_count($string, $format = 0, $charlist = null)
    {
        if ($charlist === null) {
            $regex = '/\\pL[\\pL\\p{Mn}\'-]*/u';
        } else {
            $split = array_map(
                'preg_quote',
                preg_split('//u', $charlist, -1, PREG_SPLIT_NO_EMPTY)
            );
            $regex = sprintf(
                '/(\\pL|%1$s)([\\pL\\p{Mn}\'-]|%1$s)*/u',
                implode('|', $split)
            );
        }
        switch ($format) {
            default:
            case 0:
                // For PHP >= 5.4.0 this is fine:
                return preg_match_all($regex, $string);

                // For PHP < 5.4 it's necessary to do this:
                // $results = null;
                // return preg_match_all($regex, $string, $results);
            case 1:
                $results = null;
                preg_match_all($regex, $string, $results);
                return $results[0];
            case 2:
                $results = null;
                preg_match_all($regex, $string, $results, PREG_OFFSET_CAPTURE);
                return empty($results[0])
                    ? array()
                    : array_combine(
                        array_map('end', $results[0]),
                        array_map('reset', $results[0])
                    );
        }
    }
}
