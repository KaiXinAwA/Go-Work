/**
 * Jobs page specific JavaScript
 */
$(document).ready(function() {
    // 初始化多选下拉框
    $('select[multiple]').select2({
        theme: 'bootstrap-5',
        width: '100%',
        placeholder: 'Select options',
        allowClear: true
    });

    // 显示/隐藏Job Query
    $('#toggleJobQuery').click(function(e) {
        e.preventDefault();
        $('#jobQuery').slideToggle();
        $(this).find('i').toggleClass('fa-chevron-down fa-chevron-up');
    });

    // 处理薪资范围选择
    $('select[name="salary_range[]"]').on('change', function() {
        var selectedRanges = $(this).val();
        if (selectedRanges && selectedRanges.length > 0) {
            // 清除原有的薪资输入框
            $('input[name="salary_min"], input[name="salary_max"]').val('');
            
            // 设置薪资范围
            var minSalary = 0;
            var maxSalary = 999999;
            
            selectedRanges.forEach(function(range) {
                var parts = range.split('-');
                if (parts.length === 2) {
                    var min = parseInt(parts[0]);
                    var max = parseInt(parts[1]);
                    
                    if (min < minSalary) minSalary = min;
                    if (max > maxSalary) maxSalary = max;
                } else if (range === '9000-above') {
                    if (9000 < minSalary) minSalary = 9000;
                }
            });
            
            // 创建隐藏的薪资输入框
            if ($('input[name="salary_min"]').length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'salary_min',
                    value: minSalary
                }).appendTo('#job-search-form');
            } else {
                $('input[name="salary_min"]').val(minSalary);
            }
            
            if ($('input[name="salary_max"]').length === 0) {
                $('<input>').attr({
                    type: 'hidden',
                    name: 'salary_max',
                    value: maxSalary
                }).appendTo('#job-search-form');
            } else {
                $('input[name="salary_max"]').val(maxSalary);
            }
        }
    });

    // 表单提交前验证
    $('#job-search-form').on('submit', function(e) {
        // 可以添加其他验证逻辑
    });

    // 清除所有过滤器
    $('#clearFilters').click(function(e) {
        e.preventDefault();
        $('#job-search-form')[0].reset();
        $('select[multiple]').val(null).trigger('change');
        $('#job-search-form').submit();
    });

    // 保存搜索条件到本地存储
    $('#saveSearch').click(function(e) {
        e.preventDefault();
        var searchParams = new URLSearchParams(window.location.search);
        localStorage.setItem('savedSearch', searchParams.toString());
        alert('Search criteria saved!');
    });

    // 加载保存的搜索条件
    $('#loadSavedSearch').click(function(e) {
        e.preventDefault();
        var savedSearch = localStorage.getItem('savedSearch');
        if (savedSearch) {
            window.location.href = window.location.pathname + '?' + savedSearch;
        } else {
            alert('No saved search found');
        }
    });
});
