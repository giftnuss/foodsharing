<?php

namespace Foodsharing\Modules\Core\DBConstants\Bell;

use Foodsharing\Modules\Bell\DTO\Bell;

/**
 * Templates for the identifiers of bells.
 *
 * @see Bell::$identifier
 */
class BellType
{
	/**
	 * The user has a new friend request. Arguments: IDs of this user and the sender of the request.
	 */
	public const BUDDY_REQUEST = 'buddy-%d-%d';
	/**
	 * A new post was written on the wall of an FSP which the user is following. Argument: ID of the FSP.
	 */
	public const FOOD_SHARE_POINT_POST = 'fairteiler-%d';
	/**
	 * Notification for ambassadors about a new FSP proposal. Argument: ID of the FSP.
	 */
	public const NEW_FOOD_SHARE_POINT = 'new-fairteiler-%d';
	/**
	 * A new forum post in a thread the user is participating in. Argument: ID of the post.
	 */
	public const NEW_FORUM_POST = 'forum-post-%d';
	/**
	 * Notification for ambassadors about a new foodsaver. Argument: the foodsaver's ID.
	 */
	public const NEW_FOODSAVER_IN_REGION = 'new-fs-%d';
	/**
	 * The creation of the foodsaver's pass has failed.
	 */
	public const PASS_CREATION_FAILED = 'pass-fail-%d';
	/**
	 * Notification for a store manager that someone wants to join a store.
	 */
	public const NEW_STORE_REQUEST = 'store-request-%d';
	/**
	 * The user's store request was accepted.
	 */
	public const STORE_REQUEST_ACCEPTED = 'store-arequest-%d';
	/**
	 * The user's store request was rejected.
	 */
	public const STORE_REQUEST_REJECTED = 'store-drequest-%d';
	/**
	 * The user was put on the waiting list (jumper) of a store.
	 */
	public const STORE_REQUEST_WAITING = 'store-wrequest-%d';
	/**
	 * Notification for a store manager that there are unconfirmed pickups.
	 */
	public const STORE_UNCONFIRMED_PICKUP = 'store-fetch-unconfirmed-%d';
	/**
	 * A new store was created.
	 */
	public const NEW_STORE = 'store-new-%d';
	/**
	 * The pickup times in a store were changed.
	 */
	public const STORE_TIME_CHANGED = 'store-time-%d';
	/**
	 * A new post was written on the wall of a store.
	 */
	public const STORE_WALL_POST = 'store-wallpost-%d';
	/**
	 * A new blog entry is created and needs to be checked.
	 */
	public const NEW_BLOG_POST = 'blog-check-%d';
	/**
	 * A new poll was created in a region or work group.
	 */
	public const NEW_POLL = 'new-poll-%d';
	/**
	 * The user's request to join a work group was accepted.
	 */
	public const WORK_GROUP_REQUEST_ACCEPTED = 'workgroup-arequest-%d';
	/**
	 * The user's request to join a work group was denied.
	 */
	public const WORK_GROUP_REQUEST_DENIED = 'workgroup-drequest-%d';
	/**
	 * A new report for a user was created.
	 */
	public const NEW_REPORT = 'new-report-%d';

	/**
	 * Creates a bell identifier from a template and an optional list of parameters.
	 */
	public static function createIdentifier(string $typeString, ...$params)
	{
		return sprintf($typeString, ...$params);
	}
}
