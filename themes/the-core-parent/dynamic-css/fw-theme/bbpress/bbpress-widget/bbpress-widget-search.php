/* bbPress Widget Search */
.widget_display_search form {
  position: relative;
}
.widget_display_search form:after {
  display: inline-block;
  width: 28px;
  height: 28px;
  text-align: center;
  line-height: 28px;
  font-family: 'FontAwesome';
  content: "\f002";
  font-size: 16px;
  font-weight: normal;
  color: <?php echo esc_js($the_core_less_variables['fw-widget-inner-title-color']); ?>;
  position: absolute;
  top: 50%;
  margin-top: -14px;
  right: 10px;
  z-index: 1;
}
.widget_display_search form input[name="bbp_search"] {
  width: 100%;
  line-height: <?php echo esc_js($the_core_less_variables['input-height-base']); ?>;
  border: none;
  color: <?php echo esc_js($the_core_less_variables['fw-widget-inner-title-color']); ?>;
  background-color: <?php echo the_core_hex2rgba($the_core_less_variables['fw-widget-input-bg'], 0.07); ?>;
  font-family: <?php echo ($the_core_less_variables['fw-buttons-font-family']); ?>;
  font-weight: <?php echo esc_js($the_core_less_variables['fw-buttons-font-weight']); ?>;
  font-style: <?php echo esc_js($the_core_less_variables['fw-buttons-font-style']); ?>;
  font-size: <?php echo esc_js($the_core_less_variables['input-font-size']); ?>;
  padding: <?php echo esc_js($the_core_less_variables['input-padding-y']); ?> <?php echo esc_js($the_core_less_variables['input-padding-x']); ?>;
}
.widget_display_search form input[name="bbp_search"]::-moz-placeholder {
  color: <?php echo the_core_hex2rgba($the_core_less_variables['fw-widget-inner-title-color'], 0.25); ?>;
  opacity: 1;
}
.widget_display_search form input[name="bbp_search"]:-ms-input-placeholder {
  color: <?php echo the_core_hex2rgba($the_core_less_variables['fw-widget-inner-title-color'], 0.25); ?>;
}
.widget_display_search form input[name="bbp_search"]::-webkit-input-placeholder {
  color: <?php echo the_core_hex2rgba($the_core_less_variables['fw-widget-inner-title-color'], 0.25); ?>;
}
.widget_display_search form input[type="submit"] {
  width: 28px;
  height: 28px;
  background-color: transparent;
  border: none;
  text-indent: 100px;
  position: absolute;
  top: 10px;
  right: 10px;
  z-index: 2;
  overflow: hidden;
}
