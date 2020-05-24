<?php

namespace App\Http\Controllers\Teams;

use App\Models\Team;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Mail;
use App\Repositories\Contracts\ITeam;
use App\Repositories\Contracts\IUser;
use App\Mail\SendInvitationToJoinTeam;
use App\Repositories\Contracts\IInvitation;

class InvitationsController extends Controller
{
    protected $invitations;
    protected $teams;
    protected $users;

    public function __construct(
        IInvitation $invitations,
        ITeam $teams,
        IUser $users
    )
    {
        $this->invitations = $invitations;
        $this->teams = $teams;
        $this->users = $users;
    }

    public function invite(Request $request, $teamId)
    {
        // teamの取得
        $team = $this->teams->find($teamId);

        $this->validate($request, [
            'email' => ['required', 'email']
        ]);
        $user = auth()->user();

        // ユーザーがteamを所有しているかどうか
        if(! $user->isOwnerOfTeam($team)){
            return response()->json([
                'email' => 'あなたはチームのオーナーではありません'
            ], 401);
        }

        // このメールアドレスに保留中の招待があるか
        if($team->hasPendingInvite($request->email)){
            return response()->json([
                'email' => 'このメールアドレスにはすでに保留中の招待があります'
            ], 422);
        }

        // メールでrecipientを取得
        $recipient = $this->users->findByEmail($request->email);

        // recipientが存在しない場合は、チームに参加するための招待状を送信
        if(! $recipient){
            $this->createInvitation(false, $team, $request->email);

            return response()->json([
                'message' => '招待を送信しました'
            ], 200);
        }

        // チームにユーザーがすでに存在するかどうかを確認します
        if($team->hasUser($recipient)){
            return response()->json([
                'email' => 'このユーザーはすでにチームメンバーです'
            ], 422);
        }

        // ユーザーにinvitationsを送信する
        $this->createInvitation(true, $team, $request->email);
        return response()->json([
            'message' => '招待を送信しました'
        ], 200);
    }

    public function resend($id)
    {
        $invitation = $this->invitations->find($id);

        $this->authorize('resend', $invitation);

        $recipient = $this->users
                        ->findByEmail($invitation->recipient_email);

        Mail::to($invitation->recipient_email)
            ->send(new SendInvitationToJoinTeam($invitation, !is_null($recipient)));

        return response()->json(['message' => '再度招待を送信しました'], 200);
    }

    public function respond(Request $request, $id)
    {
        $this->validate($request, [
            'token' => ['required'],
            'decision' => ['required']
        ]);

        $token = $request->token;
        $decision = $request->decision; // 'accept' or 'deny'
        $invitation = $this->invitations->find($id);

        // invitationsがこのユーザーのものかどうかを確認
        $this->authorize('respond', $invitation);


        // tokenが一致するか確認
        if($invitation->token !== $token){
            return response()->json([
                'message' => 'トークンが無効です'
            ], 401);
        }

        // acceptedかチェック
        if($decision !== 'deny'){
            $this->invitations->addUserToTeam($invitation->team, auth()->id());
        }

        $invitation->delete();

        return response()->json(['message' => '承認されました'], 200);

    }

    public function destroy($id)
    {
        $invitation = $this->invitations->find($id);
        $this->authorize('delete', $invitation);

        $invitation->delete();

        return response()->json(['message' => '削除しました'], 200);
    }

    public function createInvitation(bool $user_exists, Team $team, string $email)
    {
        $invitation = $this->invitations->create([
            'team_id' => $team->id,
            'sender_id' => auth()->id(),
            'recipient_email' => $email,
            'token' => md5(uniqid(microtime()))
        ]);

        Mail::to($email)
            ->send(new SendInvitationToJoinTeam($invitation, $user_exists));
    }

}
