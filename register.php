<?php

$REGISTER_LTI2 = array(
"name" => "PDF Annotator",
"FontAwesome" => "fa-file-pdf-o",
"short_name" => "PDF Grader",
"description" => "A tool to turn in PDF files and allow those files to be annotated and graded.",
    // By default, accept launch messages..
    "messages" => array("launch"),
    "privacy_level" => "name_only",  // anonymous, name_only, public
    "license" => "Apache",
    "languages" => array(
        "English",
    ),
    "source_url" => "https://github.com/tsugitools/pdfannotate",
    // For now Tsugi tools delegate this to /lti/store
    "placements" => array(
        /*
        "course_navigation", "homework_submission",
        "course_home_submission", "editor_button",
        "link_selection", "migration_selection", "resource_selection",
        "tool_configuration", "user_navigation"
        */
    ),
    "screen_shots" => array(
    )

);
