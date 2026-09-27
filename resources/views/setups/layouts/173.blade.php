<div class="space-y-2 max-w-6xl mx-auto">
    @if(!empty($alerts))
    <div class="mb-4 space-y-2">

        @foreach($alerts as $alert)
            <div class="
                px-3 py-2 rounded text-sm
                {{ $alert['type'] === 'danger' ? 'bg-red-500 text-white' : '' }}
                {{ $alert['type'] === 'warning' ? 'bg-yellow-400 text-black' : '' }}
            ">
                {{ $alert['name'] }}
            </div>
        @endforeach

    </div>
@endif
    <div x-data="{ view: 'all' }" class="space-y-4">

        @include('setups.layouts.mainbar')

        {{-- FRONT AERO --}}
        <div class="grid grid-cols-5 gap-2">
            <div x-show="view==='all'">
                <x-setup.tyre-telemetry-card 
                title="FL Tyre Telemetry" 
                :data="$telemetry['fl']"
                :wear="$telemetryWear['fl']"
                position="fl"/>
            </div>
            <div x-show="view==='all'">
                <x-setup.card title="" :items="$zones['telemetry_FL']" type="telemetry"/>
            </div>
            <div x-show="view==='all' || view==='aero'">
                <x-setup.card title="Front Aero" :items="$zones['front_aero']" type="aero"/></div>
            <div x-show="view==='all'">
                <x-setup.card title="" :items="$zones['telemetry_FR']" type="telemetry"/>
            </div>
            <div x-show="view==='all'">
                <x-setup.tyre-telemetry-card 
                title="FL Tyre Telemetry" 
                :data="$telemetry['fr']"
                :wear="$telemetryWear['fr']"
                position="fr"/>
            </div>
        </div>

        {{-- FRONT --}}
        <div class="grid grid-cols-5 gap-2">
            
            <div x-show="view==='all' || view==='tyre'">
                <x-setup.card title="FL Tyre" :items="$zones['fl_tyre']" type="tyre"/>
            </div>
            
            <div x-show="view==='all' || view==='susp'">
                <x-setup.card title="FL Susp" :items="$zones['fl_susp']" type="susp"/>
            </div>

            <div x-show="view==='all' || view==='chassis'">
                <x-setup.card title="Front Chassis" :items="$zones['front_chassis']" type="chassis"/>
            </div>

            <div x-show="view==='all' || view==='susp'">
                <x-setup.card title="FR Susp" :items="$zones['fr_susp']" type="susp"/>
            </div>

            <div x-show="view==='all' || view==='tyre'">
                <x-setup.card title="FR Tyre" :items="$zones['fr_tyre']" type="tyre"/>
            </div>

        </div>

        {{-- FRONT CHASSIS --}}
        <div class="grid grid-cols-5 gap-2">
            <div x-show="view==='all'"></div>
            <div x-show="view==='all'"></div>
            <div x-show="view==='all' || view==='chassis'">
                <x-setup.card title="Main Chassis" :items="$zones['main_chassis']" type="chassis"/>
            </div>
            <div x-show="view==='all'">
                <x-setup.card title="config" :items="$zones['config']" type="config"/>
            </div>
            <div>
                
            </div>
        </div>

        {{-- CENTER --}}
        <div class="grid grid-cols-5 gap-2">
            <div x-show="view==='all'">
                <x-setup.tyre-telemetry-card 
                title="RL Tyre Telemetry" 
                :data="$telemetry['rl']"
                :wear="$telemetryWear['rl']"
                position="rl"/>
            </div>
            <div x-show="view==='all' || view==='susp'">
                <x-setup.card title="RL Susp" :items="$zones['rl_susp']" type="susp"/>
            </div>
            <div x-show="view==='all' || view==='chassis'">
                <x-setup.card title="Rear Chassis" :items="$zones['rear_chassis']" type="chassis"/>
            </div>
            <div x-show="view==='all' || view==='susp'">
                <x-setup.card title="RR Susp" :items="$zones['rr_susp']" type="susp"/>
            </div>
            <div x-show="view==='all'">
                <x-setup.tyre-telemetry-card 
                title="RR Tyre Telemetry" 
                :data="$telemetry['rr']"
                :wear="$telemetryWear['rr']"
                position="rr"/>
            </div>
        </div>

         {{-- CENTER --}}
         <div class="grid grid-cols-5 gap-2">
                <div x-show="view==='all' || view==='tyre'">
                <x-setup.card title="RL Tyre" :items="$zones['rl_tyre']" type="tyre"/></div>
                <div x-show="view==='all'">
                <x-setup.card title="" :items="$zones['telemetry_RL']" type="telemetry"/></div>
                <div x-show="view==='all' || view==='aero'">
                <x-setup.card title="Rear Aero" :items="$zones['rear_aero']" type="aero"/></div>
                <div x-show="view==='all'">
                <x-setup.card title="" :items="$zones['telemetry_RR']" type="telemetry"/></div>
                <div x-show="view==='all' || view==='tyre'">
                <x-setup.card title="RR Tyre" :items="$zones['rr_tyre']" type="tyre"/></div>
        </div>
    

        {{-- telemetry --}}
        <div class="grid grid-cols-5 gap-2" x-show="view==='telemetry'">
            <div><x-setup.tyre-telemetry-card 
                title="FL Tyre Telemetry" 
                :data="$telemetry['fl']"
                :wear="$telemetryWear['fl']"
                position="fl"/></div>
            <div><x-setup.card title="" :items="$zones['telemetry_FL']" type="telemetry"/></div>
            <div><x-setup.card title="" :items="$zones['telemetry_FR']" type="telemetry"/></div>
            <div><x-setup.tyre-telemetry-card 
                title="FR Tyre Telemetry" 
                :data="$telemetry['fr']"
                :wear="$telemetryWear['fr']"
                position="fr"/></div>
            <div></div>
        </div>

        <div class="grid grid-cols-5 gap-2" x-show="view==='telemetry'">
            <div><x-setup.tyre-telemetry-card 
                title="RL Tyre Telemetry" 
                :data="$telemetry['rl']"
                :wear="$telemetryWear['rl']"
                position="rl"/></div>
            <div><x-setup.card title="" :items="$zones['telemetry_RL']" type="telemetry"/></div>
            <div><x-setup.card title="" :items="$zones['telemetry_RR']" type="telemetry"/></div>
            <div><x-setup.tyre-telemetry-card 
                title="RR Tyre Telemetry" 
                :data="$telemetry['rr']"
                :wear="$telemetryWear['rr']"
                position="rr"/></div>
            <div></div>
        </div>


        
    </div>
</div>