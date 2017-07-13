<?php
namespace Qazana;

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

class Widget_Text_Editor extends Widget_Base {

	public function get_name() {
		return 'text-editor';
	}

	public function get_title() {
		return __( 'Text Editor', 'qazana' );
	}

	public function get_icon() {
		return 'eicon-align-left';
	}

	protected function _register_controls() {
		$this->start_controls_section(
			'section_editor',
			[
				'label' => __( 'Text Editor', 'qazana' ),
			]
		);

		$this->add_control(
			'editor',
			[
				'label' => '',
				'type' => Controls_Manager::WYSIWYG,
				'default' => __( 'I am a text block. Click the edit button to change this text. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Ut elit tellus, luctus nec ullamcorper mattis, pulvinar dapibus leo.', 'qazana' ),
			]
		);

		$this->add_responsive_control(
			'max_width',
			[
				'label' => _x( 'Max width', 'Size Control', 'qazana' ),
				'type' => Controls_Manager::SLIDER,
				'size_units' => [ 'px', 'em', 'rem' ],
				'range' => [
					'px' => [
						'min' => 10,
						'max' => 2000,
					],
				],
				'responsive' => true,
				'selectors' => [
					'{{WRAPPER}} .qazana-wrapper' => 'max-width: {{SIZE}}{{UNIT}};',
				],
			]
		);

		$this->add_responsive_control(
			'align',
			[
				'label' => __( 'Alignment', 'qazana' ),
				'type' => Controls_Manager::CHOOSE,
				'options' => [
					'left' => [
						'title' => __( 'Left', 'qazana' ),
						'icon' => 'fa fa-align-left',
					],
					'center' => [
						'title' => __( 'Center', 'qazana' ),
						'icon' => 'fa fa-align-center',
					],
					'right' => [
						'title' => __( 'Right', 'qazana' ),
						'icon' => 'fa fa-align-right',
					],
					'justify' => [
						'title' => __( 'Justified', 'qazana' ),
						'icon' => 'fa fa-align-justify',
					],
				],
				'prefix_class' => 'qazana-align-',
				'selectors' => [
					'{{WRAPPER}} .qazana-wrapper' => 'text-align: {{VALUE}};',
				],
			]
		);

		$this->end_controls_section();

		$this->start_controls_section(
			'section_style',
			[
				'label' => __( 'Text Editor', 'qazana' ),
				'tab' => Controls_Manager::TAB_STYLE,
			]
		);

	    $this->add_control(
	        'text_color',
	        [
	            'label' => __( 'Text Color', 'qazana' ),
	            'type' => Controls_Manager::COLOR,
	            'default' => '',
	            'selectors' => [
	                '{{WRAPPER}} *' => 'color: {{VALUE}};',
	            ],
	            'scheme' => [
		            'type' => Scheme_Color::get_type(),
		            'value' => Scheme_Color::COLOR_3,
	            ],
	        ]
	    );

		$this->add_group_control(
			Group_Control_Typography::get_type(),
			[
				'name' => 'typography',
				'scheme' => Scheme_Typography::TYPOGRAPHY_3,
				'selector' => '{{WRAPPER}} *',
			]
		);

		$this->end_controls_section();
	}

	protected function render() {

        $settings = $this->get_settings();

        $this->add_render_attribute( 'heading', 'class', 'qazana-text-editor entry-content qazana-clearfix' );

		$editor_content = $this->parse_text_editor( $settings['editor'] );

		?><div <?php echo $this->get_render_attribute_string( 'heading' ); ?>><div class="qazana-wrapper"><?php echo $settings['editor']; ?></div></div><?php

	}

	public function render_plain_content() {
		// In plain mode, render without shortcode
		echo $this->get_settings( 'editor' );
	}

	protected function _content_template() {
		?>

		<div class="qazana-text-editor entry-content qazana-clearfix"><div class="qazana-wrapper">{{{ settings.editor }}}</div></div>
		<?php
	}
}
