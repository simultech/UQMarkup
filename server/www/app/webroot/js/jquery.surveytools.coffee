###
Survey Tools plugin.
Author: Justin Marrington

Provides some basic tools for asynchronous survey question posting, and hiding/showing
of sections in a survey.
###

$ = jQuery

# FIXME: reset link from testing url base to /_dev/ base
settings = 
  postbackUrl: '/surveys/savesurvey'
  yesSubquestionClass: 'yes'
  noSubquestionClass: 'no'
  questionClass: 'surveyquestion'
  submitSelector: 'input[type="submit"]'
  

methods = 
  init: (options) ->
    # Call the initialiser on the survey container element
    # Sets up subquestion containment, among other things
    @each () ->
      settings = $.extend settings, options
      $(this).find ''
      
  destroy: () ->
    @each () ->
      $(window).off '.surveytools'
      
  conditionalSections: () ->
    questionGroups = {}
    $(".#{settings.questionClass}").each () ->
      if $(this).is(".#{settings.yesSubquestionClass}, .#{settings.noSubquestionClass}")
        parentClass = $(this).data 'parent_id'
        if parentClass of questionGroups
          group = questionGroups[parentClass]
          group.push $(this)
        else
          questionGroups[parentClass] = [$(this)]
        $(this).remove()
      else
        yes_selector = $(this).find 'input.yes_selector'
        $(this).find('input.yes_selector').on 'change', 
            {'selectedYes': true}, methods.toggleSection
        $(this).find('input.no_selector').on 'change', 
            {'selectedYes': false}, methods.toggleSection
    
    for group, elements of questionGroups
      container = $("<div class='subquestions' data-parent_id='#{group}' />")
      yes_container = $('<div class="yes_subquestions" />')
      no_container = $('<div class="no_subquestions" />')
      parent_question = $('#question_' + group)
      parent_question.after container
      container.append yes_container
      container.append no_container
      yes_response = parent_question.find 'input.yes_selector'
      no_response = parent_question.find 'input.no_selector'
      unless yes_response.attr 'checked'
        yes_container.hide()
      unless no_response.attr 'checked'
        no_container.hide()
      
      
      for element in elements
        if element.hasClass settings.yesSubquestionClass
          yes_container.append element
        else
          no_container.append element
      
    return $(this)
        
          
  toggleSection: (event) ->
    # Shows and hides the "Yes" or "No" subsections of parent questions
    # 
    showYes = event.data.selectedYes
    parent = $("#question_" + $(this).data('parent_id'))
    
    if showYes
      el = parent.next(".subquestions").children('.yes_subquestions:hidden')
      no_el = parent.next(".subquestions").children('.no_subquestions').not(":hidden")
      no_el.slideUp 'fast'
      el.slideDown 'fast'
    else
      el = parent.next(".subquestions").children('.no_subquestions:hidden')
      yes_el = parent.next(".subquestions").children('.yes_subquestions').not(":hidden")
      el.slideDown 'fast'
      yes_el.slideUp 'fast'
  
  saveSurvey: () ->
    # Serializes responses and asynchronously submits
    @each () ->
      result_label = $('span#submit_result')
      $(this).on 'submit', (event) ->
        event.preventDefault()
        formdata = $(this).serialize()
        survey_name = $(this).data 'survey_name'
        project_id = $(this).data 'project_id'
        $.post "#{settings.postbackUrl}/#{survey_name}/#{project_id}", 
          formdata,
          (response) ->
            if response is "1"
              # show survey saved message
              result_label.text "Saved successfully"
              result_label.attr "class", ""
              result_label.addClass("success")
            else
              result_label.prepend "Couldn't save your responses"
              result_label.attr "class", ""
              result_label.addClass("failure")
            result_label.fadeIn "fast", () ->
              result_label.delay(3000).fadeOut "slow"
      
      
    

$.fn.surveytools = (method) ->
    if methods[method]
      methods[method].apply this, Array.prototype.slice.call arguments, 1
    else if typeof method == 'object' or not method
      methods.init.apply this, arguments
    else
      $.error "Method " + method + " does not exist on plugin surveytools."