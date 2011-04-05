<?php

/*
 * Copyright 2011 Facebook, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *   http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

class PhabricatorObjectSelectorDialog {

  private $user;
  private $filters = array();
  private $handles = array();
  private $cancelURI;
  private $submitURI;
  private $noun;
  private $searchURI;

  public function setUser($user) {
    $this->user = $user;
    return $this;
  }

  public function setFilters(array $filters) {
    $this->filters = $filters;
    return $this;
  }

  public function setHandles(array $handles) {
    $this->handles = $handles;
    return $this;
  }

  public function setCancelURI($cancel_uri) {
    $this->cancelURI = $cancel_uri;
    return $this;
  }

  public function setSubmitURI($submit_uri) {
    $this->submitURI = $submit_uri;
    return $this;
  }

  public function setSearchURI($search_uri) {
    $this->searchURI = $search_uri;
    return $this;
  }

  public function setNoun($noun) {
    $this->noun = $noun;
    return $this;
  }

  public function buildDialog() {
    $user = $this->user;

    $filter_id = celerity_generate_unique_node_id();
    $query_id = celerity_generate_unique_node_id();
    $results_id = celerity_generate_unique_node_id();
    $current_id = celerity_generate_unique_node_id();
    $search_id  = celerity_generate_unique_node_id();
    $form_id = celerity_generate_unique_node_id();

    require_celerity_resource('phabricator-object-selector-css');

    $options = array();
    foreach ($this->filters as $key => $label) {
      $options[] = phutil_render_tag(
        'option',
        array(
          'value' => $key
        ),
        $label);
    }
    $options = implode("\n", $options);

    $search_box = phabricator_render_form(
      $user,
      array(
        'method' => 'POST',
        'action' => $this->submitURI,
        'id'     => $search_id,
      ),
      '<table class="phabricator-object-selector-search">
        <tr>
          <td class="phabricator-object-selector-search-filter">
            <select id="'.$filter_id.'">'.
              $options.
            '</select>
          </td>
          <td class="phabricator-object-selector-search-text">
            <input type="text" id="'.$query_id.'" />
          </td>
        </tr>
      </table>');
    $result_box =
      '<div class="phabricator-object-selector-results" id="'.$results_id.'">'.
      '</div>';
    $attached_box =
      '<div class="phabricator-object-selector-current">'.
        '<div class="phabricator-object-selector-currently-attached">'.
          '<div class="phabricator-object-selector-header">'.
            'Currently Attached '.$this->noun.
          '</div>'.
          '<div id="'.$current_id.'">'.
          '</div>'.
        '</div>'.
      '</div>';


    $dialog = new AphrontDialogView();
    $dialog
      ->setUser($this->user)
      ->setTitle('Manage Attached '.$this->noun)
      ->setClass('phabricator-object-selector-dialog')
      ->appendChild($search_box)
      ->appendChild($result_box)
      ->appendChild($attached_box)
      ->setRenderDialogAsDiv()
      ->setFormID($form_id)
      ->addSubmitButton('Save '.$this->noun);

    if ($this->cancelURI) {
      $dialog->addCancelButton($this->cancelURI);
    }

    $handle_views = array();
    foreach ($this->handles as $phid => $handle) {
      $view = new PhabricatorHandleObjectSelectorDataView($handle);
      $handle_views[$phid] = $view->renderData();
    }
    $dialog->addHiddenInput('phids', implode(';', array_keys($this->handles)));


    Javelin::initBehavior(
      'phabricator-object-selector',
      array(
        'filter'  => $filter_id,
        'query'   => $query_id,
        'search'  => $search_id,
        'results' => $results_id,
        'current' => $current_id,
        'form'    => $form_id,
        'uri'     => $this->searchURI,
        'handles' => $handle_views,
      ));

   return $dialog;
  }

}
