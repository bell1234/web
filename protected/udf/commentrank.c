   #ifdef STANDARD
   /* STANDARD is defined, don't use any mysql functions */
   #include <stdlib.h>
   #include <stdio.h>
   #include <string.h>
   #ifdef __WIN__
   typedef unsigned __int64 ulonglong;/* Microsofts 64 bit types */
   typedef __int64 longlong;
   #else
   typedef unsigned long long ulonglong;
   typedef long long longlong;
   #endif /*__WIN__*/
   #else
   #include <my_global.h>
   #include <my_sys.h>
   #if defined(MYSQL_SERVER)
   #include <m_string.h>/* To get strmov() */
   #else
   /* when compiled as standalone */
   #include <string.h>
   #define strmov(a,b) stpcpy(a,b)
   #define bzero(a,b) memset(a,0,b)
   #define memcpy_fixed(a,b,c) memcpy(a,b,c)
   #endif
   #endif
   #include <mysql.h>
   #include <ctype.h>
   
   #ifdef HAVE_DLOPEN
   
   #if !defined(HAVE_GETHOSTBYADDR_R) || !defined(HAVE_SOLARIS_STYLE_GETHOST)
   static pthread_mutex_t LOCK_hostname;
   #endif
   
   #include <math.h>
   
   my_bool commentrank_init(UDF_INIT *initid, UDF_ARGS *args, char *message);
   void commentrank_deinit(UDF_INIT *initid __attribute__((unused)));
   double commentrank(UDF_INIT* initid, UDF_ARGS* args __attribute__((unused)),
                     char* is_null __attribute__((unused)), char* error __attribute__((unused)));
   
   my_bool commentrank_init(UDF_INIT *initid, UDF_ARGS *args, char *message)
   {
     if(!(args->arg_count == 2)) {
       strcpy(message, "Expected two arguments");
       return 1;
     }
   
     args->arg_type[0] = REAL_RESULT;
     args->arg_type[1] = REAL_RESULT;
   
     return 0;
   }
   
   void commentrank_deinit(UDF_INIT *initid __attribute__((unused)))
   {
   
   }
   
   double commentrank(UDF_INIT* initid, UDF_ARGS* args __attribute__((unused)),
                     char* is_null __attribute__((unused)), char* error __attribute__((unused)))
   {
     double ups = *((double *)(args->args[0]));
     double downs = *((double *)(args->args[1]));

     double final = 0.0;

       double n = ups + downs;
       if(n == 0.0){
		return 0.0;
        }
       double z = 1.281551565545;
       double p = ups / n;

       double left = p + 1/(2*n)*z*z;
       double right = z*sqrt(p*(1-p)/n + z*z/(4*n*n));
       double under = 1+1/n*z*z;
   
       double result = (left - right) / under;

       final = round( 100000000.0 * result ) / 100000000.0;

       return final;
   }

   #endif /* HAVE_DLOPEN */

