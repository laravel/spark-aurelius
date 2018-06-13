<spark-kiosk-teams :team="team" inline-template>
    <div>
        <div v-show=" ! showingTeamProfile">
            <!-- Search Field card -->
            <div class="card card-default" style="border: 0;">
                <div class="card-body">
                    <form role="form" @submit.prevent>
                        <!-- Search Field -->
                        <input type="text" id="kiosk-teams-search" class="form-control"
                                name="search"
                                placeholder="{{__('Search By Name...')}}"
                                v-model="teamSearchForm.query"
                                @keyup.enter="search">
                    </form>
                </div>
            </div>

            <!-- Searching -->
            <div class="card card-default" v-if="searching">
                <div class="card-header">{{__('Search Results')}}</div>

                <div class="card-body">
                    <i class="fa fa-btn fa-spinner fa-spin"></i> {{__('Searching')}}
                </div>
            </div>

            <!-- No Search Results -->
            <div class="card card-default" v-if=" ! searching && noTeamSearchResults">
                <div class="card-header">{{__('Search Results')}}</div>

                <div class="card-body">
                    {{__('No teams matched the given criteria.')}}
                </div>
            </div>

            <!-- Team Search Results -->
            <div class="card card-default" v-if=" ! searching && teamSearchResults.length > 0">
                <div class="card-header">{{__('Search Results')}}</div>

                <div class="table-responsive">
                    <table class="table table-valign-middle mb-0">
                        <thead>
                            <th></th>
                            <th>{{__('Name')}}</th>
                            <th>{{__('Owner')}}</th>
                            <th class="th-fit"></th>
                        </thead>

                        <tbody>
                            <tr v-for="searchTeam in teamSearchResults">
                                <!-- Profile Photo -->
                                <td>
                                    <img :src="searchTeam.photo_url" class="spark-profile-photo">
                                </td>

                                <!-- Name -->
                                <td>
                                    <div class="btn-table-align">
                                        @{{ searchTeam.name }}
                                    </div>
                                </td>

                                <!-- Owner -->
                                <td>
                                    <div class="btn-table-align">
                                        @{{ searchTeam.owner.name }}
                                    </div>
                                </td>

                                <td>
                                    <!-- View Team Profile -->
                                    <button class="btn btn-default" @click="showTeamProfile(searchTeam)">
                                        <i class="fa fa-search"></i>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Team Profile Detail -->
        <div v-show="showingTeamProfile">
            @include('spark::kiosk.team-profile')
        </div>
    </div>
</spark-kiosk-teams>
