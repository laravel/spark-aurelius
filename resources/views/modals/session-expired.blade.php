<!-- Session Expired Modal -->
<div class="modal fade" id="modal-session-expired" tabindex="-1" role="dialog" data-backdrop="static" data-keyboard="false">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">
                    {{__('Session Expired')}}
                </h4>
            </div>

            <div class="modal-body">
                {{__('Your session has expired. Please login again to continue.')}}
            </div>

            <!-- Modal Actions -->
            <div class="modal-footer">
                <a href="/login">
                    <button type="button" class="btn btn-default">
                        <i class="fa fa-btn fa-sign-in"></i> {{__('Go To Login')}}
                    </button>
                </a>
            </div>
        </div>
    </div>
</div>
