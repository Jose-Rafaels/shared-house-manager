<?php

namespace App\Http\Controllers;

use App\Http\Requests\MemberRequest;
use App\Models\Member;
use App\Services\ActivityLogService;

class MemberController extends Controller
{
    public function index()
    {
        return view('members.index', [
            'members' => Member::query()->orderBy('name')->get(),
        ]);
    }

    public function store(MemberRequest $request, ActivityLogService $activityLogService)
    {
        $member = Member::query()->create($request->validated());
        $activityLogService->log('member.created', "Added member {$member->name}.", $member);

        return back()->with('status', 'Member added.');
    }

    public function update(MemberRequest $request, Member $member, ActivityLogService $activityLogService)
    {
        $member->update($request->validated());
        $activityLogService->log('member.updated', "Updated member {$member->name}.", $member);

        return back()->with('status', 'Member updated.');
    }

    public function destroy(Member $member, ActivityLogService $activityLogService)
    {
        $name = $member->name;
        $member->delete();
        $activityLogService->log('member.archived', "Archived member {$name}.", $member);

        return back()->with('status', 'Member archived.');
    }
}
