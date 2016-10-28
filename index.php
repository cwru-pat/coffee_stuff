<?php

$page_id = "page_home";
$mathjax = true;

require_once "private/site.php";

require_once "private/templates/header.php";
require_once "private/templates/navbar.php";
require_once "private/templates/jumbotron.php";

?>
<script type="text/javascript" src="<?php print path(); ?>js/toggle.js"></script>
<script type="text/javascript" src="<?php print path(); ?>js/sidebar.js"></script>
<div class="container">
    <div class="row">
  
        <div class="col-sm-3" id="leftCol">
            <div class="btn-group-vertical toggle-content" id="sidebar-list" role="group" data-clampedwidth="#leftCol">
                <div class="list-group-item active">
                    <h5 class="list-group-item-heading"> arXiv <span class="hidden-sm hidden-xs hidden-md">display</span> toggles:</h5>
                </div>
                <div class="list-group-item">
                    <?php require_once "private/templates/calendar.php"; ?>
                </div>
                <div class="btn-group-vertical toggle-content sidebar-scrollable" id="arxiv-toggle-list" role="group" data-clampedwidth="#leftCol">
                </div>
            </div>
        </div>

        <div class="col-sm-9" id="feedCol">
            <?php require_once "private/templates/feed.php"; ?>
        </div>

    </div>
</div>

<?php
require_once "private/templates/footer.php";
