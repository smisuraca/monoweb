//if (ScriptEngine && ScriptEngineMajorVersion()+'.'+ScriptEngineMinorVersion() < 5.5)
var undefined;

if (!Function.prototype.apply) {
        Function.prototype.apply = function bu_fix_apply(o,a) {
                var r;
                if (!o) o = {};
                o.___apply=this;
                switch((a && a.length) || 0) {
                case 0: r = o.___apply(); break;
                case 1: r = o.___apply(a[0]); break;
                case 2: r = o.___apply(a[0],a[1]); break;
                case 3: r = o.___apply(a[0],a[1],a[2]); break;
                case 4: r = o.___apply(a[0],a[1],a[2],a[3]); break;
                case 5: r = o.___apply(a[0],a[1],a[2],a[3],a[4]); break;
                case 6: r = o.___apply(a[0],a[1],a[2],a[3],a[4],a[5]); break;
                default:
                        for (var i=0, s=""; i<a.length;i++) {
                                if(i!=0) s += ",";
                                s += "a[" + i +"]";
                        }
                        r = eval("o.___apply(" + s + ")");
                }
                o.__apply = null;
                return r;
        };
}

if (!Function.prototype.call) {
        Function.prototype.call = function bu_fix_call(o) {
                var args = new Array(arguments.length - 1);
                for(var i=1;i<arguments.length;i++) {args[i - 1] = arguments[i];}
                return this.apply(o, args);
        };
}

if (!Array.prototype.push) {
        Array.prototype.push = function bu_fix_push() {
                for (var i = 0; i < arguments.length; i++) {this[this.length] = arguments[i];}
                return this.length;
        };
}

if (!Array.prototype.pop) {
        Array.prototype.pop = function bu_fix_pop() {
                if (this.length == 0) return undefined;
                return this[this.length--];
        };
}

if (!Array.prototype.shift) {
        Array.prototype.shift = function bu_fix_shift() {
                this.reverse();
                var lastv = this.pop();
                this.reverse();
                return lastv;
        };
}

if (!Array.prototype.splice) {
        Array.prototype.splice = function bu_fix_splice(start, deleteCount) {
                var len = parseInt(this.length);
                start = start ? parseInt(start) : 0;
                start = (start < 0) ? Math.max(start+len,0) : Math.min(len,start);
                deleteCount = deleteCount ? parseInt(deleteCount) : 0;
                deleteCount = Math.min(Math.max(parseInt(deleteCount),0), len);
                var deleted = this.slice(start, start+deleteCount);
                var insertCount = Math.max(arguments.length - 2,0);
                var new_len = this.length + insertCount - deleteCount;
                var start_slide = start + insertCount;
                var nslide = len - start_slide;
                for(var i=new_len - 1;i>=start_slide;--i) {this[i] = this[i - nslide];}
                for(i=start;i<start+insertCount;++i) {this[i] = arguments[i-start+2];}
                return deleted;
        };
}

if (!Array.prototype.unshift) {
        Array.prototype.unshift = function bu_fix_unshift() {
                var a = [0,0];
                for(var i=0;i<arguments.length;i++) {a.push(arguments[i]);}
                var ret = this.splice.apply(a);
                return this.length;
        };
}

if (!Number.prototype.toFixed) {
        function bu_nz(n) {return n <= 0 ? '' : '0000000000000000000000000'.substring(25 - n)}
        Number.prototype.toFixed = function(fracDigits) {
                var f = this;
                if (typeof fracDigits == 'undefined') fracDigits = 0;
                if (fracDigits < 0) throw Error("negative fracDigits " + fracDigits);
                var n = Math.round(Math.abs(f) * Math.pow(10, fracDigits));
                var s;
                if (isNaN(n) || n == 2147483647) {
                        s = String(f);
                        var dec = s.indexOf('.');
                        if (dec == -1) return fracDigits > 0 ? s + '.' + bu_nz(fracDigits) : s;
                        var res = s.substring(0,dec+1);
                        var fraction = s.substring(dec+1);
                        if (fraction.length >= fracDigits) return res + fraction.substring(0,fracDigits);
                        res = res + fraction + bu_nz(fracDigits - fraction.length);
                        return res;
                }
                s = String(n);
                if (fracDigits > 0) {
                if (s.length > fracDigits)
                        s = s.substring(0, s.length - fracDigits) + '.' + s.substring(s.length - fracDigits);
                else {
                        s = '0.' + bu_nz(fracDigits - s.length) + s;
                }
        };
        if (f < 0) s = '-' + s;
                return s;
        }
}

if (!Number.prototype.toPrecision) {
        Number.prototype.toPrecision = function(prec) {
                var f = this;
                if (typeof prec == 'undefined') return String(f);
                if (prec < 0) throw Error("negative precision " + prec);
                if (Number.prototype.$$toPrecision$$ ) {
                        var nat = Number.prototype.$$toPrecision$$.call(this, prec);
                        if (/e/i.test(nat)) return nat;
                }
                var exp = Math.floor(Math.log(Math.abs(f))/Math.LN10 + 0.000001);
                if (exp >= prec) {
                        s = this.toExponential(prec - 1);
                }
                else {
                        var n = Math.round(Math.abs(f) * Math.pow(10, prec - exp - 1));
                        s = String(n);
                        var nshift = prec - exp - 1;
                        if (nshift == 0) {
                        }
                        else if (nshift < s.length) {
                                s = s.substring(0, s.length - nshift) + '.' + s.substring(s.length - nshift);
                        }
                        else {
                                s = '0.' + bu_nz(nshift - s.length) + s;
                        }
                }
                return s;
        };
}

if (!Number.prototype.toExponential) {
        Number.prototype.toExponential = function(fracDigits) {
                var f = this;
                var exp = Math.floor(Math.log(Math.abs(f))/Math.LN10 + 0.000001);
                var n;
                if (typeof fracDigits == 'undefined') {
                        n = Math.abs(f) * Math.pow(10, 0 - exp - 1);
                }
                else {
                        n = Math.round(Math.abs(f) * Math.pow(10, fracDigits - exp));
                }
                var s = String(n).replace(/(\d)/, "$1.");
                s += (exp >= 0 ? 'e+' : 'e') + exp;
                if (f < 0) s = '-' + s;
                return s;
        };
}
