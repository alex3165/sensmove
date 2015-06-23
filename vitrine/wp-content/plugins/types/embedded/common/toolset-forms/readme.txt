Usage:

include 'common/forms/bootstrap.php';
// API call, returns HTML formatted output
$html = wptoolset_form_field( $field_db_data );

To get HTML and rest of the scripts queued,
call function before queue_styles WP hook.


Filters:

toolset_valid_image_extentions
toolset_valid_video_extentions

Parameters:
- array - valid extension to be filtered

Output:
- array - filtered extension array

Example: add jfif extension:

add_filter( 'toolset_valid_image_extentions', 'my_toolset_valid_image_extentions' );
function my_toolset_valid_image_extentions($valid_extensions)
{
    $valid_extensions[] = 'jfif';
    return $valid_extensions;
}

= Changelog =

2015-03-25

- Fixed missing warning for date type field.
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/196695908/comments

2015-02-06

- Fixed empty object in WPV_Handle_Users_Functions class when user is
  not logged always return false.
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/194981023/comments

2014-12-29

- fixed display CPT in CT

2014-11-18 - plugins release - CRED 1.3.4, Types 1.6.4

2014-11-13

- Fixed a problem with missing taxonomies after form fail:
  https://wp-types.com/forums/topic/cred-featured-image-and-tag-selector-empty-after-validation-refresh/

2014-11-10

- Fixed a problem with datepicker witch do not working inside a modal
  dialog
  https://wp-types.com/forums/topic/cred-forms-not-displaying-on-tabletmobile-browsers/

2014-11-03

- add filters to change taxonomies buttons text:
  - toolset_button_show_popular_text
  - toolset_button_hide_popular_text
  - toolset_button_add_new_text
  - toolset_button_cancel_text
  - toolset_button_add_text

- add filters to change repetitive buttons text:
  - toolset_button_delete_repetition_text
  - toolset_button_add_repetition_text
  https://wp-types.com/forums/topic/format-form-field-as-list/#post-255070
  https://wp-types.com/forums/topic/hi/
  https://wp-types.com/forums/topic/how-to-change-wpt-repdelete-button-value/

2014-10-23
- Fixed issue with missing previously saved data.
  https://wp-types.com/forums/topic/date-not-recorded-in-a-postype
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/191003870/comments#comment_296586170

- Fixed a problem with not working build-in taxonomies (category,
  post_tag) when we use this in CPT and this post are not included on
  frontend.
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/190833102/comments

- Fixed a problem with WYSIWYG field description.
  https://wp-types.com/forums/topic/add-a-link-in-the-custom-field-description/
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/191030746/comments

2014-10-21
- Fixed issue on checkbox after submit - there was wrong condition to
  display checked checkbox.
  https://wp-types.com/forums/topic/checkbox-value-not-saved/
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/190834414/comments

2014-10-13

- Fixed a wrong error message position, was under date field.
  https://wp-types.com/forums/topic/some-issues-and-feedback-on-cred-1-3-3/
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/190493441/comments

2014-10-10

- Improved - add class for li element for checkboxes, radio, taxonomy
  (both: flat and hierarchical), this class is based on checkbox label
  and is sanitizet by "sanitize_title" function.
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/170609656/comments
  http://wp-types.com/forums/topic/ugly-cred-taxonomy-cannot-style/

- Added filter "cred_item_li_class" which allow to change class of LI
  element in checkboxes, radio and hierarchical taxonomy field.

2014-10-09

- Fixed warning on user site, when CRED is not installed and we check
  CRED setting
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/190474262/comments
  https://wp-types.com/forums/topic/i-just-updated-types-and-im-getting-this-error/

- Fixed problem with validation if is empty conditions, validation
  should return true, not false.
  https://wp-types.com/forums/topic/custom-types-fields-not-saving-changes/
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/190347235/comments#295158276

- Improved taxonomy buttons by adding extra class "btn-cancel" when it
  is needed - on "Cancel" for hierarchical and on "Hide" on
  non-hierarchical.
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/190492232/comments

2014-10-07

- Fixed problem with replacing @ char from filename
  https://wp-types.com/forums/topic/types-sanitizes-sign-from-file-names/

2014-10-03

- Fixed a problem with abandon filter wpcf_fields_*_value_get - this
  groups of filters was not copy to common library.
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/189095886/comments
  http://wp-types.com/forums/topic/default-field-value-custom-function-no-longer-works/

2014-10-01

- Fixed a problem with not changed label, when adding new taxonomy.
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/190086914/comments

- Fixed changing the file name when upload the file
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/189560556/comments
  http://wp-types.com/forums/topic/types-1-6-update-breaks-layout-that-worked-in-types-1-5-7/

2014-09-30
- Fixed a problem with multiple CRED form on one screen.
  https://icanlocalize.basecamphq.com/projects/7393061-toolset/todo_items/189954041/comments
  http://wp-types.com/forums/topic/cred-conditional-group-3/

