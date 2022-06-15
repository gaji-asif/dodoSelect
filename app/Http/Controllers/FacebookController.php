<?php

namespace App\Http\Controllers;
use App\Jobs\FacebookAutoReplyJob;
use App\Models\FacebookAutoreply;
use App\Models\FacebookAutoreplyCampaign;
use App\Models\FacebookPage;
use App\Models\FacebookUser;
use Facebook\Exceptions\FacebookResponseException;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class FacebookController extends Controller {
    public $fb;
    public function __construct()
    {
        $this->fb = new Facebook([
            'app_id' => env('FB_APP_ID'),
            'app_secret' => env('FB_APP_SECRET'),
            'default_graph_version' => 'v2.10',
        ]);
        $this->middleware('auth')->except('facebookWebhook');
    }

    public function index()
    {
        $pages = FacebookUser::getAuthUserPages();
        return view('facebook.settings', compact('pages'));
    }

    public function facebookAuthorization()
    {
        try {
            $helper = $this->fb->getRedirectLoginHelper();
            $permissions = ['email','pages_manage_posts','pages_manage_engagement','pages_manage_metadata','pages_read_engagement','pages_show_list','pages_messaging','public_profile','read_insights'];
            $loginURL = $helper->getLoginUrl(route('facebook.auth.redirect'), $permissions);
            return redirect()->to($loginURL);
        } catch (FacebookSDKException $e) {}
        return false;
    }

    public function authRedirect(Request $request)
    {
        $redirect_url = route('facebook.auth.redirect').'?code='.$request->code.'&state='.$request->state;
        $helper = $this->fb->getRedirectLoginHelper();
        $data = [];
        $accessToken = null;
        try {
            $accessToken = $helper->getAccessToken($redirect_url);
            $response = $this->fb->get('/me?fields=id,name,email,picture', $accessToken);
            $data['user'] = $response->getGraphUser()->asArray();
        } catch (FacebookSDKException $e) {
            session()->flash('msg', $e->getMessage());
            return redirect()->to(route('facebook.index'));
        }

        $oAuth2Client = $this->fb->getOAuth2Client();
        if (! $accessToken->isLongLived()) {
            try {
                $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken)->getValue();
                if(isset($longLivedAccessToken)):
                    $fb_user = FacebookUser::where('fb_id', $data['user']['id'])->first();
                    if($fb_user == null):
                        $fb_user = new FacebookUser();
                    endif;
                    $fb_user->user_id = Auth::id();
                    $fb_user->access_token = $longLivedAccessToken;
                    $fb_user->name = $data['user']['name'];
                    $fb_user->fb_profile_photo = json_encode($data['user']['picture']);
                    $fb_user->email = isset($data['user']['email']) ? $data['user']['email'] : "no-email@facebook.com";
                    $fb_user->fb_id = $data['user']['id'];
                    $fb_user->save();
                    $this->facebookPageList($data['user']['id']);
                    $data['error'] = false;
                    session()->flash('msg', __('translation.Facebook account has been authorized successfully!'));
                endif;
            } catch (FacebookSDKException $e) {
                session()->flash('msg', $e->getMessage());
            }
        } else {
            $fb_user = FacebookUser::where('fb_id', $data['user']['id'])->first();
            if($fb_user == null):
                $fb_user = new FacebookUser();
            endif;
            $fb_user->user_id = Auth::id();
            $fb_user->access_token = $accessToken->getValue();
            $fb_user->name = $data['user']['name'];
            $fb_user->fb_profile_photo = json_encode($data['user']['picture']);
            $fb_user->email = isset($data['user']['email']) ? $data['user']['email'] : "no-email@facebook.com";
            $fb_user->fb_id = $data['user']['id'];
            $fb_user->save();
            $this->facebookPageList($data['user']['id']);
            $data['error'] = false;
            session()->flash('msg', __('translation.Facebook account has been authorized successfully!'));
        }

        return redirect()->to(route('facebook.index'));
    }

    private function facebookPageList($fb_id)
    {
        $facebookUser = FacebookUser::where('fb_id', $fb_id)->first();
        if($facebookUser != null):
            $response = $this->fb->get('/me/accounts?fields=cover,emails,picture,id,name,url,username,access_token&limit=400', $facebookUser->access_token);
            $pages = $response->getGraphEdge()->asArray();
            foreach ($pages as $page):
                $new_page = FacebookPage::where('page_id', $page['id'])->first();
                if($new_page == null):
                    $new_page = new FacebookPage();
                endif;
                $new_page->user_id = Auth::id();
                $new_page->fb_id = $fb_id;
                $new_page->page_id = $page['id'];
                $new_page->page_cover = isset($page['cover']) ? json_encode($page['cover']) : json_encode(['url'=>'']);

                $fileName = isset($page['username']) ? $page['username'] : $page['id'];
                $upload = file_get_contents($page['picture']['url']);
                Storage::disk('s3')->put('img/fbPages/'.$fileName.'.jpg', $upload, 'public');
                $new_page->page_profile = json_encode(['url' => 'img/fbPages/'.$fileName.'.jpg']);
                $new_page->page_name = $page['name'];
                $new_page->username = isset($page['username']) ? $page['username'] : "Not Found";
                $new_page->page_access_token = $page['access_token'];
                $new_page->page_email = isset($page['emails']) ? $page['emails'][0] : "no-email@facebook.com";
                if($new_page->save()):
                    $this->facebookPageSubscribe($page['id'], $page['access_token']);
                endif;
            endforeach;
            return true;
        endif;
        return false;
    }

    private function facebookPageSubscribe($page_id, $access_token)
    {
        $params = array();
        $params['subscribed_fields'] = array("messages","messaging_optins","messaging_postbacks","messaging_referrals", "feed");
        $this->fb->post(
            '/'.$page_id.'/subscribed_apps',
            $params,
            $access_token
        );
        return true;
    }

    public function facebookPageDelete($id, Request $request)
    {
        if($request->ajax()):
            $page = FacebookPage::find($id);
            if($page->users->user_id == Auth::id()):
                if($page != null):
                    $page->delete();
                    $replies = FacebookAutoreply::where('page_id', $page->id)->get();
                    foreach ($replies as $reply):
                        $reply->delete();
                    endforeach;
                    return response()->json([
                        'message' => __('translation.Facebook page has been deleted successfully!')
                    ]);
                endif;
            endif;
        endif;
        return response()->json([
            'message' => __('translation.Something went wrong!')
        ]);
    }

    public function facebookPageEdit(Request $request)
    {
        if ($request->ajax()) {
            if (isset($request->id) && $request->id != null) {
                $data['page'] = FacebookPage::find($request->id);
                $data['replies'] = FacebookAutoreply::where('user_id', Auth::id())
                    ->where('page_id', $request->id)
                    ->get();
                return view('facebook.elements.autoreply-campaign', $data);
            }
        }
        return false;
    }

    public function facebookAutoReplyCampaign(Request $request)
    {
        // Delete words if doesn't match with DB
        $req_words = $request->filterWords;
        $db_words = FacebookAutoreply::where('page_id', $request->page_id)->get('filter_words')->toArray();
        if(count($req_words) != count($db_words)):
            $trashes = array_diff(array_column($db_words, 'filter_words'), $req_words);
            foreach($trashes as $trash):
                $word = FacebookAutoreply::where('filter_words', $trash)->first();
                $word->delete();
            endforeach;
        endif;

        //Save or update new words
        for($i=0;$i<count($request->filterWords);$i++):
            if(isset($request->filterWords[$i])):
                $reply = FacebookAutoreply::where('filter_words', 'like','%'.$request->filterWords[$i].'%')->first();
                if($reply == null):
                    $reply = new FacebookAutoreply();
                endif;
                $reply->page_id = $request->page_id;
                $reply->user_id = Auth::id();
                $reply->filter_words = $request->filterWords[$i];
                $reply->comment_body = $request->commentBody[$i];
                $reply->private_message = $request->privateMessage[$i];
                $reply->save();
            endif;
        endfor;

        //Save generic response
        $page = FacebookPage::find($request->page_id);
        $page->generic_comment_reply = isset($request->generic_comment_reply) ? $request->generic_comment_reply : "";
        $page->generic_private_reply = isset($request->generic_private_reply) ? $request->generic_private_reply : "";
        $page->save();

        session()->flash('msg', __('translation.Auto-replies has been saved successfully!'));
        return redirect()->back();
    }

    public function facebookWebhook(Request $request)
    {
        $response = json_encode($request->all());
        $response = json_decode($response);

        if(isset($response->entry)):
            $data['page_id'] = $response->entry[0]->id;
            $data['comment_id'] = isset($response->entry[0]->changes[0]->value->comment_id) ? $response->entry[0]->changes[0]->value->comment_id : "";
            $data['commenter_id'] = isset($response->entry[0]->changes[0]->value->from->id) ? $response->entry[0]->changes[0]->value->from->id : "";
            $data['commenter_name'] = isset($response->entry[0]->changes[0]->value->from->name) ? $response->entry[0]->changes[0]->value->from->name : "";
            $data['message'] = isset($response->entry[0]->changes[0]->value->message) ? $response->entry[0]->changes[0]->value->message : "";

            $page = FacebookPage::where('page_id', $data['page_id'])->first();
            if($page != null && !empty($data['comment_id']) && !empty($data['message'])):
                if($data['commenter_name'] != $page->page_name):
                    FacebookAutoReplyJob::dispatch($data);
                endif;
            endif;
        endif;
        echo $request->hub_challenge;
        exit();
    }
}
