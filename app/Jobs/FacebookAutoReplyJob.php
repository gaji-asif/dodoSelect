<?php

namespace App\Jobs;

use App\Models\FacebookAutoreply;
use App\Models\FacebookPage;
use Facebook\Exceptions\FacebookSDKException;
use Facebook\Facebook;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class FacebookAutoReplyJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $commentData;
    public $fb;
    public function __construct($commentData)
    {
        $this->commentData = $commentData;
        $this->fb = new Facebook([
            'app_id' => env('FB_APP_ID'),
            'app_secret' => env('FB_APP_SECRET'),
            'default_graph_version' => 'v2.10',
        ]);
    }

    /**
     * Execute the job.
     *
     * @return void
     * @throws FacebookSDKException
     */
    public function handle()
    {
        $page = FacebookPage::where('page_id', $this->commentData['page_id'])->first();
        $comment = null; $thanks = "Thank you so much :)";
        $private = null;
        if($page != null):
            $replies = FacebookAutoreply::where('page_id', $page->id)->get();
            foreach ($replies as $reply):
                $words = explode(',', $reply->filter_words);
                if(0 < count(array_intersect(array_map('strtolower',
                        explode(' ', $this->commentData['message'])), $words))):
                    $comment = $reply->comment_body;
                    $private = strlen(trim($reply->private_message)) > 0 ? $reply->private_message : $private;
                endif;
            endforeach;

            if($comment == null):
                $comment = strlen(trim($page->generic_comment_reply)) > 0 ? $page->generic_comment_reply : $thanks;
            endif;

            if($private == null):
                $private = strlen(trim($page->generic_private_reply)) > 0 ? $page->generic_private_reply : $thanks;
            endif;

            //Like the user's comment
            $like = [];
            $like["likes.summary"] = true;
            $this->fb->post(
                '/'.$this->commentData['comment_id'].'/likes',
                $like,
                $page->page_access_token
            );

            sleep(1);

            //Reply to the comment
            if($page->autoreply_enabled == 'yes'):
                $params = array();
                $params['message'] = '@['.$this->commentData['commenter_id'].']'.' '.$comment;
                $this->fb->post(
                    '/'.$this->commentData['comment_id'].'/comments',
                    $params,
                    $page->page_access_token
                );
            endif;

            sleep(1);

            //Private reply
            $data['recipient'] = ['comment_id' => $this->commentData['comment_id']];
            $data['message'] = ['text' => $private];
            Http::withHeaders([
                'Content-Type' => 'application/json',
            ])->post('https://graph.facebook.com/v12.0/me/messages?access_token='.$page->page_access_token, $data);
        endif; // end IF page!=null
    }
}
