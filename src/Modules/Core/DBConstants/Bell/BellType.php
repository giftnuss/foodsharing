<?php

namespace Foodsharing\Modules\Core\DBConstants\Bell;

class BellType
{
	/**
	 * The user has a new friend request.
	 */
	public const BUDDY_REQUEST = 'buddy-';
	/**
	 * A new post was written on the wall of an FSP which the user is following.
	 */
	public const FOOD_SHARE_POINT_POST = 'fairteiler-';
	/**
	 * Notification for ambassadors about a new FSP proposal.
	 */
	public const NEW_FOOD_SHARE_POINT = 'new-fairteiler-';
	/**
	 * A new forum post in a thread the user is participating in.
	 */
	public const NEW_FORUM_POST = 'forum-post-';
	/**
	 * Notification for ambassadors about a new foodsaver.
	 */
	public const NEW_FOODSAVER_IN_REGION = 'new-fs-';
	/**
	 * The creation of the foodsaver's pass has failed.
	 */
	public const PASS_CREATION_FAILED = 'pass-fail-';
	/**
	 * Notification for a store manager that someone wants to join a store.
	 */
	public const NEW_STORE_REQUEST = 'store-request-';
	/**
	 * The user's store request was accepted.
	 */
	public const STORE_REQUEST_ACCEPTED = 'store-arequest-';
	/**
	 * The user's store request was rejected.
	 */
	public const STORE_REQUEST_REJECTED = 'store-drequest-';
	/**
	 * The user was put on the waiting list (jumper) of a store.
	 */
	public const STORE_REQUEST_WAITING = 'store-wrequest-';
	/**
	 * Notification for a store manager that there are unconfirmed pickups.
	 */
	public const STORE_UNCONFIRMED_PICKUP = 'store-fetch-unconfirmed-';
	/**
	 * A new store was created.
	 */
	public const NEW_STORE = 'store-new-';
	/**
	 * The pickup times in a store were changed.
	 */
	public const STORE_TIME_CHANGED = 'store-time-';
	/**
	 * A new post was written on the wall of a store.
	 */
	public const STORE_WALL_POST = 'store-wallpost-';
	/**
	 * A new blog entry is created and needs to be checked.
	 */
	public const NEW_BLOG_POST = 'blog-check-';
	/**
	 * A new poll was created in a region or work group.
	 */
	public const NEW_POLL = 'new-poll-';
	/**
	 * The user's request to join a work group was accepted.
	 */
	public const WORK_GROUP_REQUEST_ACCEPTED = 'workgroup-arequest-';
	/**
	 * The user's request to join a work group was denied.
	 */
	public const WORK_GROUP_REQUEST_DENIED = 'workgroup-drequest-';
}
