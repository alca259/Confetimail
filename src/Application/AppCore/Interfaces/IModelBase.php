<?php
interface IModelBase
{
	/**
	 * Create a new record in DB from a specified model
	 * @param int $uid
	 * @param array(objects) $vals
	 * @param array(objects) $context
	 * @return int Id New Record
     * @throws ORMException if error
	 */
	public function Create($uid, $vals, $context);

	/**
	 * Modify a record in DB from a specified model
	 * @param int $uid
	 * @param int $ids
	 * @param array(objects) $vals
	 * @param array(objects) $context
     * @throws ORMException if error
	 */
	public function Write($uid, $ids, $vals, $context);

	/**
	 * Delete a record in DB from a specified model
	 * @param int $uid
	 * @param array(int) $ids
	 * @param array(objects) $context
     * @throws ORMException if error
	 */
	public function Unlink($uid, $ids, $context);

	/**
	 * Search IDS from a domain array
	 * @param int $uid
	 * @param array(string) $domain
     * @param string column $order_by
     * @param int $limitResults
     * @param int $limitStart
	 * @return array(int) Ids of results
     * @throws ORMException if error
	 */
	public function Search($uid, $domain = array(), $order_by = "", $limitResults = "", $limitStart = "");

	/**
	 * Recover array of data from one ids
	 * @param int $uid
	 * @param array(int) $ids
     * @param string column $order_by
     * @param int $limitResults
     * @param int $limitStart
	 * @return array of records
     * @throws ORMException if error
	 */
	public function Browse($uid, $ids = array(), $order_by = "", $limitResults = "", $limitStart = "");

	/**
	 * Recover current objects from one ids
	 * @param int $uid
	 * @param array(int) $ids
	 * @param array(objects) $context
	 * @return array of objects with data and functions
     * @throws ORMException if error
	 */
	public function BrowseRecord($uid, $ids = array(), $order_by = "", $context = array());
    
    /**
     * Count records with a domain array
     * @param int $uid
     * @param array(string) $domain
     * @return int Number of results
     * @throws ORMException if error
     */
    public function Count($uid, $domain = array());
}
?>