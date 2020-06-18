<?php

namespace Drupal\uc_cdr_client\Entity;

class Publication {
  protected $title;
  protected $author_list;
  protected $citation;
  protected $pmid;
  protected $publicationName;
  protected $publicationVenue;
  protected $date;
  protected $year;
  protected $linkUrl;
  protected $drupal_id;

  /**
   * @return mixed
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * @param mixed $title
   */
  public function setTitle($title) {
    $this->title = $title;
  }

  /**
   * @return mixed
   */
  public function getAuthorList() {
    return $this->author_list;
  }

  /**
   * @param mixed $author_list
   */
  public function setAuthorList($author_list) {
    $this->author_list = $author_list;
  }

  /**
   * @return mixed
   */
  public function getCitation() {
    return $this->citation;
  }

  /**
   * @param mixed $citation
   */
  public function setCitation($citation) {
    $this->citation = $citation;
  }

  /**
   * @return mixed
   */
  public function getPmid() {
    return $this->pmid;
  }

  /**
   * @param mixed $pmid
   */
  public function setPmid($pmid) {
    $this->pmid = $pmid;
  }

  /**
   * @return mixed
   */
  public function getPublicationName() {
    return $this->publicationName;
  }

  /**
   * @param mixed $publicationName
   */
  public function setPublicationName($publicationName) {
    $this->publicationName = $publicationName;
  }

  /**
   * @return mixed
   */
  public function getPublicationVenue() {
    return $this->publicationVenue;
  }

  /**
   * @param mixed $publicationVenue
   */
  public function setPublicationVenue($publicationVenue) {
    $this->publicationVenue = $publicationVenue;
  }

  /**
   * @return mixed
   */
  public function getDate() {
    return $this->date;
  }

  /**
   * @param mixed $date
   */
  public function setDate($date) {
    $this->date = $date;
  }

  /**
   * @return mixed
   */
  public function getYear() {
    return $this->year;
  }

  /**
   * @param mixed $year
   */
  public function setYear($year) {
    $this->year = $year;
  }

  /**
   * @return mixed
   */
  public function getLinkUrl() {
    return $this->linkUrl;
  }

  /**
   * @param mixed $linkUrl
   */
  public function setLinkUrl($linkUrl) {
    $this->linkUrl = $linkUrl;
  }

  /**
   * @return mixed
   */
  public function getDrupalId() {
    return $this->drupal_id;
  }

  /**
   * @param mixed $drupal_id
   */
  public function setDrupalId($drupal_id) {
    $this->drupal_id = $drupal_id;
  }
}
