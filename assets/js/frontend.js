(function ($) {
	"use strict";
	
	Dropzone.autoDiscover = false;
	
	$(document).ready(function(){
		
		/*--------------------------------------------------*/
		/*  Mobile Menu - mmenu.js
		/*--------------------------------------------------*/
		$(function() {
			function mmenuInit() {
				var wi = $(window).width();
				if(wi <= '1024') {

					$(".mmenu-init" ).remove();
					$("#navigation").clone().addClass("mmenu-init").insertBefore("#navigation").removeAttr('id').removeClass('style-1 style-2').find('ul').removeAttr('id');

					$(".mmenu-init").mmenu({
						"counters": true,
						navbar: {
							title: "Menu"
						  }
					}, {
					 // configuration
					 offCanvas: {
						pageNodetype: "#wrapper"
					 }
					});

					var mmenuAPI = $(".mmenu-init").data( "mmenu" );
					var $icon = $(".hamburger");
					mmenuAPI.close();
					$icon.removeClass( "is-active" );
					$('#mm-blocker').hide();
					$(".mmenu-trigger").click(function() {
						mmenuAPI.open();
					});

					mmenuAPI.bind( "open:finish", function() {
					   setTimeout(function() {
						  $icon.addClass( "is-active" );
					   });
					});
					mmenuAPI.bind( "close:finish", function() {
					   setTimeout(function() {
						  $icon.removeClass( "is-active" );
					   });
					});


				}
				$(".mm-next").addClass("mm-fullsubopen");
			}
			mmenuInit();
			$(window).resize(function() { mmenuInit(); });
		});
		
		/*  User Menu */
		$('body').on('click', '.user-menu', function(){
			$(this).toggleClass('active');
		});

		var user_mouse_is_inside = false;

		$("body" ).on( "mouseenter", ".user-menu", function() {
			user_mouse_is_inside=true;
		});
		$("body" ).on( "mouseleave", ".user-menu" ,function() {
			user_mouse_is_inside=false;
		});

		$("body").mouseup(function(){
			if(! user_mouse_is_inside) $(".user-menu").removeClass('active');
		});
		
		/*----------------------------------------------------*/
		/* Dashboard Scripts
		/*----------------------------------------------------*/
		$('.dashboard-nav ul li a').on('click', function(){
			if ($(this).closest('li').has('ul').length) {
				$(this).parent('li').toggleClass('active');
			}
		});

		/* Dashbaord Nav Scrolling */
		$(window).on('load resize', function() {
			var wrapperHeight = window.innerHeight;
			var headerHeight = $("#header-container").height();
			var winWidth = $(window).width();

			if(winWidth>992) {
				$(".dashboard-nav-inner").css('max-height', wrapperHeight-headerHeight);
			} else {
				$(".dashboard-nav-inner").css('max-height', '');
			}
		});
		
		/* Responsive Nav Trigger */
		$('.dashboard-responsive-nav-trigger').on('click', function(e){
			e.preventDefault();
			$(this).toggleClass('active');

			var dashboardNavContainer = $('body').find(".dashboard-nav");

			if( $(this).hasClass('active') ){
				$(dashboardNavContainer).addClass('active');
			} else {
				$(dashboardNavContainer).removeClass('active');
			}

		});
		
		/*----------------------------------------------------*/
		/*  Documents Upload
		/*----------------------------------------------------*/
		if ($("#media-uploader._documents").length > 0) {
			/* Upload using dropzone */
			
			var documentsDropzone = new Dropzone("#media-uploader._documents", {
				url: sa_core.document_upload,
				timeout: 999999,
				maxFiles:sa_core.maxFiles,
				dictDefaultMessage: sa_core.dictDefaultMessage,
				dictFallbackMessage: sa_core.dictFallbackMessage,
				dictFallbackText: sa_core.dictFallbackText,
				dictFileTooBig: sa_core.dictFileTooBig,
				dictInvalidFileType: sa_core.dictInvalidFileType,
				dictResponseError: sa_core.dictResponseError,
				dictCancelUpload: sa_core.dictCancelUpload,
				dictCancelUploadConfirmation: sa_core.dictCancelUploadConfirmation,
				dictRemoveFile: sa_core.dictRemoveFile,
				acceptedFiles: 'application/pdf',
				init: function() {
					this.on("addedfile", function(file){
						/* Set active thumb class to preview that is used as thumbnail*/
			  
						if(file['attachment_id'] === parseInt($('#_thumbnail_id').val())) {
							file.previewElement.className += ' active-thumb _documents'+file['attachment_id'];
						} else {
							file.previewElement.className += ' _documents'+ parseInt(file['attachment_id']);
						}
						 file.previewElement.addEventListener("click", function() {
							$('.dz-preview').removeClass('active-thumb');
							$(this).addClass('active-thumb'); 
						 
						   var id = file['attachment_id'];  
						   $('#_thumbnail_id').val(id); 
						});
					})
					,
					this.on("complete", function(file){
						file.previewElement.className += ' _documents'+file.attachment_id;
					});
				},
				success: function (file, response) {
					file.previewElement.classList.add("dz-success");
					file['attachment_id'] = response; // push the id for future reference
					
					$("#media-uploader-ids").append('<input id="_documents' + file['attachment_id'] +'" type="hidden" name="_documents[' +file['attachment_id']+ ']"  value="'+file['name']+'">');
				},
				error: function (file, response) {
					file.previewElement.classList.add("dz-error");
					$(file.previewElement).find('.dz-error-message').text(response);
				},
				addRemoveLinks: true,
				removedfile: function(file) {
					var attachment_id = file['attachment_id'];   
					$('input#_documents'+attachment_id).remove();
					/*remove thumbnail if the image was set as it*/
					if($('#_thumbnail_id').val() == attachment_id){
						$('#_thumbnail_id').val('');
					}
					$.ajax({
						type: 'POST',
						url: sa_core.document_delete,
						data: {
							media_id : attachment_id
						}, 
						success: function (result) {

						   console.log(result);
						},
						error: function () {
							console.log("delete error");
						}
					});
					var _ref;
					return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;        
				}
			});
		}
		
		/*----------------------------------------------------*/
		/*  Avatar Upload
		/*----------------------------------------------------*/
		if ($("#avatar-uploader").length > 0) {
			/* Upload using dropzone */
			
			var avatarDropzone = new Dropzone("#avatar-uploader", {
				url: sa_core.upload,
				maxFiles:1,
				dictDefaultMessage: sa_core.dictDefaultMessage,
				dictFallbackMessage: sa_core.dictFallbackMessage,
				dictFallbackText: sa_core.dictFallbackText,
				dictFileTooBig: sa_core.dictFileTooBig,
				dictInvalidFileType: sa_core.dictInvalidFileType,
				dictResponseError: sa_core.dictResponseError,
				dictCancelUpload: sa_core.dictCancelUpload,
				dictCancelUploadConfirmation: sa_core.dictCancelUploadConfirmation,
				dictRemoveFile: sa_core.dictRemoveFile,
				dictMaxFilesExceeded: sa_core.dictMaxFilesExceeded,
				acceptedFiles: 'image/*',
				accept: function(file, done) {
				 
				  done();
				},
				init: function() {
					this.on("addedfile", function() {
						if (this.files[1]!=null){
							this.removeFile(this.files[0]);
						}
					});
				},   

				success: function (file, response) {
					file.previewElement.classList.add("dz-success");
					file['attachment_id'] = response; // push the id for future reference
					$("#avatar-uploader-id").val(file['attachment_id']);

				},
				error: function (file, response) {
					file.previewElement.classList.add("dz-error");
				},
				// update the following section is for removing image from library
				addRemoveLinks: true,
				removedfile: function(file) {
				  var attachment_id = file['attachment_id'];
					$("#avatar-uploader-id").val('');
					$.ajax({
						type: 'POST',
						url: sa_core.delete,
						data: {
							media_id : attachment_id
						}, 
						success: function (result) {
							 console.log(result);
						  },
						  error: function () {
							  console.log("delete error");
						  }
					});
					var _ref;
					return (_ref = file.previewElement) != null ? _ref.parentNode.removeChild(file.previewElement) : void 0;        
				}
			});

			avatarDropzone.on("maxfilesexceeded", function(file) {
				this.removeFile(file);
			});
			if($('.edit-profile-photo').attr('data-photo')){
				var mockFile = { name: $('.edit-profile-photo').attr('data-name'), size: $('.edit-profile-photo').attr('data-size') };
				avatarDropzone.emit("addedfile", mockFile);
				avatarDropzone.emit("thumbnail", mockFile, $('.edit-profile-photo').attr('data-photo'));
				avatarDropzone.emit("complete", mockFile);
				avatarDropzone.files.push(mockFile);
				// If you use the maxFiles option, make sure you adjust it to the
				// correct amount:
				  
				avatarDropzone.options.maxFiles = 1;
			}

		}
		
		/*----------------------------------------------------*/
		/*  Notifications
		/*----------------------------------------------------*/
		$("a.close").removeAttr("href").on('click', function(){

			function slideFade(elem) {
				var fadeOut = { opacity: 0, transition: 'opacity 0.5s' };
				elem.css(fadeOut).slideUp();
			}
			slideFade($(this).parent());

		});
		
		/*----------------------------------------------------*/
		/*  Chosen Plugin
		/*----------------------------------------------------*/

		var config = {
		  '.chosen-select'           : {
				disable_search_threshold: 10, 
				width:"100%",
				no_results_text: sa_core.no_results_text,
				placeholder_text_single:  sa_core.placeholder_text_single,
				placeholder_text_multiple: sa_core.placeholder_text_multiple
			},
		  '.chosen-select-deselect'  : {
				allow_single_deselect:true, 
				width:"100%",
				no_results_text: sa_core.no_results_text
			},
		  '.chosen-select-no-single' : {
				disable_search_threshold:100, 
				width:"100%",
				no_results_text: sa_core.no_results_text,
				placeholder_text_single:  sa_core.placeholder_text_single,
				placeholder_text_multiple: sa_core.placeholder_text_multiple
			},
		  '.chosen-select-no-single.no-search' : {
				disable_search_threshold:10, 
				width:"100%",
				no_results_text: sa_core.no_results_text,
				placeholder_text_single:  sa_core.placeholder_text_single,
				placeholder_text_multiple: sa_core.placeholder_text_multiple
			},
		  '.chosen-select-no-results': {
				no_results_text: sa_core.no_results_text,
				placeholder_text_single:  sa_core.placeholder_text_single,
				placeholder_text_multiple: sa_core.placeholder_text_multiple
		  },
		  '.chosen-select-width'     : {
				width:"95%",
				no_results_text: sa_core.no_results_text,
				placeholder_text_single:  sa_core.placeholder_text_single,
				placeholder_text_multiple: sa_core.placeholder_text_multiple
		  }
		};

		for (var selector in config) {
			if (config.hasOwnProperty(selector)) {
				$(selector).chosen(config[selector]);
			}
		}
		
		$(document).on('change', 'select.doc-tag', function() {
			var $this = $(this);
			var ajax_data = {
				'action': 'update_user_document_tag',
				'documentID': $this.attr('docID'),
				'tag': $this.val()
			};
			$.ajax({
				type: 'POST',
				dataType: 'json',
				url: sa_core.ajax_url, 
				data: ajax_data,
				success: function(data) {
				}
			});
		});
		
		$(document).on('change', 'select[name="filter_tag"]', function() {
			var $this = $(this);
			window.location.href = $this.find(':selected').data('url');
		});
		
	});
	
})(this.jQuery);