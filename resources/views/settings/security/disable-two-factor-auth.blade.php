<spark-disable-two-factor-auth :user="user" inline-template>
    <div class="card card-default">
        <div class="card-body">
            <button class="btn btn-outline-danger" @click="disable" :disabled="form.busy">
                <span v-if="form.busy">
                    <i class="fa fa-btn fa-spinner fa-spin"></i> {{__('Disabling')}}
                </span>

                <span v-else>
                    {{__('Disable Two-Factor Authentication')}}
                </span>
            </button>
        </div>
    </div>
</spark-disable-two-factor-auth>
