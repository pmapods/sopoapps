<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Auth;

class hasMenuAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $access)
    {
        $access = explode(':',$access);
        $menu_acceses = explode("|",$access[1]);
        foreach($menu_acceses as $key=>$menu_access){
            $menu_acceses[$key] = intval($menu_access);
        }
        if($access[0] == 'masterdata'){
            $emp_access = intval(Auth::user()->menu_access->masterdata ?? 0);
            foreach($menu_acceses as $key=>$menu_access){
                if(($emp_access & $menu_access) != 0){
                    return $next($request);
                }
            }    
            return redirect('/dashboard')->with('error','Anda tidak memiliki access ke menu bersangkutan. Silahkan hubungi developer untuk mendapatkan akses');
        }
        
        if($access[0] == 'budget'){
            $emp_access = intval(Auth::user()->menu_access->budget ?? 0);
            foreach($menu_acceses as $key=>$menu_access){
                if(($emp_access & $menu_access) != 0){
                    return $next($request);
                }
            }    
            return redirect('/dashboard')->with('error','Anda tidak memiliki access ke menu bersangkutan. Silahkan hubungi developer untuk mendapatkan akses');
        }
        
        if($access[0] == 'operational'){
            $emp_access = intval(Auth::user()->menu_access->operational ?? 0);
            foreach($menu_acceses as $key=>$menu_access){
                if(($emp_access & $menu_access) != 0){
                    return $next($request);
                }
            }    
            return redirect('/dashboard')->with('error','Anda tidak memiliki access ke menu bersangkutan. Silahkan hubungi developer untuk mendapatkan akses');
        }

        if($access[0] == 'monitoring'){
            $emp_access = intval(Auth::user()->menu_access->monitoring ?? 0);
            foreach($menu_acceses as $key=>$menu_access){
                if(($emp_access & $menu_access) != 0){
                    return $next($request);
                }
            }    
            return redirect('/dashboard')->with('error','Anda tidak memiliki access ke menu bersangkutan. Silahkan hubungi developer untuk mendapatkan akses');
        }

        if($access[0] == 'reporting'){
            $emp_access = intval(Auth::user()->menu_access->reporting ?? 0);
            foreach($menu_acceses as $key=>$menu_access){
                if(($emp_access & $menu_access) != 0){
                    return $next($request);
                }
            }    
            return redirect('/dashboard')->with('error','Anda tidak memiliki access ke menu bersangkutan. Silahkan hubungi developer untuk mendapatkan akses');
        }

        if($access[0] == 'feature'){
            $emp_access = intval(Auth::user()->menu_access->feature ?? 0);
            foreach($menu_acceses as $key=>$menu_access){
                if(($emp_access & $menu_access) != 0){
                    return $next($request);
                }
            }    
            return redirect('/dashboard')->with('error','Anda tidak memiliki access ke menu bersangkutan. Silahkan hubungi developer untuk mendapatkan akses');
        }
    }
}
