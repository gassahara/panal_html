      function LZW_compress (uncompressed_from) {
	  var uncompressed=btoa(uncompressed_from);
	  var y=0;
          var i,
              dictionary = {},
              c,
              wc,
              w = "",
              result = [],
              dictSize = 256;
	  var base64 = [
  'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
  'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
  'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
  'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
	      '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '+', '/'
	  ];
	  dictSize = base64.length;
          for (i = 0; i < dictSize; i += 1) {
              dictionary[base64[i]] = i;
          }
          for (i = 0; i < uncompressed.length; i += 1) {
              c = uncompressed.charAt(i);
              wc = w + c;
              if (dictionary.hasOwnProperty(wc)) {
                  w = wc;
              } else {
                  result[y]=dictionary[w];
		  y++;
                  dictionary[wc] = dictSize++;
                  w = String(c);
              }
          }
          if (w !== "") {
              result[y]=dictionary[w];
	      y++;
          }
	  var rr="";
	  var nn="";
	  var mm=[];
	  var bitlength = Math.floor(Math.log2(dictSize)) + 1;
	  for(r=0; r<result.length; r+=1){
	      nn=result[r].toString(2);
	      while(nn.length<bitlength) nn="0"+nn;
	      while(nn.length>bitlength) nn=nn.substring(1, nn.length);
	      rr+=nn;	      
	  }
	  nn="";
	  for (let i = 0; i < rr.length; i += 8) {
	      nn+=String.fromCharCode(parseInt(rr.substring(i, i + 8), 2));
	  }
	  console.log("compressed", {uncompressed, bitlength, nn}, nn.length, uncompressed_from.length);
          return String.fromCharCode(bitlength)+nn;
      }

      function LZW_decompress  (compressed) {
	  const bitlength=compressed.charCodeAt(0);
	  var dcompressed="";
	  var nn="";
	  for(r=1; r<compressed.length; r+=1){
	      nn=compressed.charCodeAt(r).toString(2);
	      while(nn.length<8) nn="0"+nn;
	      while(nn.length>8) nn=nn.substring(1, nn.length);
	      dcompressed+=nn;
	  }
	  var bcompressed=[];
	  var j=0;
	  for (let i = 0; i < dcompressed.length; i += bitlength) {
	      bcompressed[j]=parseInt(dcompressed.substring(i, i + bitlength), 2);
	      j++;
	  }	  
	  console.log("compressed", bitlength, nn.length, nn, bcompressed, (dcompressed.length/bitlength));
          var i,
              dictionary = [],
              w,
              result,
              k,
              entry = "",
              dictSize = 256;
          for (i = 0; i < 256; i += 1) {
              dictionary[i] = String.fromCharCode(i);
          }
	  dictionary = [
  'A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M',
  'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z',
  'a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm',
  'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z',
	      '0', '1', '2', '3', '4', '5', '6', '7', '8', '9', '+', '/'
	  ];	  
	  dictSize = dictionary.length;
          w = dictionary[bcompressed[0]];
          result = w;
          for (i = 1; i < bcompressed.length; i += 1) {
              k = bcompressed[i];
              if (dictionary[k]) {
                  entry = dictionary[k];
              } else {
                  if (k === dictSize) {
                      entry = w + w.charAt(0);
                  } else {
                      continue;
                  }
              }
              result += entry;
              dictionary[dictSize++] = w + entry.charAt(0);
              w = entry;
          }
	  console.log("compressed", {result});
          return atob(result);
      }

