// SPDX-License-Identifier: MIT
pragma solidity >=0.8.7;

import './IERC20.sol';
import './IUniswapV2Router02.sol';
import './IUniswapV2Pair.sol';
import './IUniswapV2Factory.sol';
import './UniswapV2Library.sol';
import "./SafeMath.sol";

  //+-(UniSwap and SushiSwap Factories S.C.s Addresses).
  /**+-Ethereum MainNet & Ropsten TestNet D.EX.s Factory Addresses:_
  +-UniSwap Factory Address = '0x5C69bEe701ef814a2B6a3EDD4B1652CB9cc5aA6f'(Is the Same in Both MainNet and TestNet).
  +-SushiSwap Factory Address = '0xC0AEe478e3658e2610c5F7A4A2E1777cE9e4f2Ac'(Is the Same in Both MainNet and TestNet).*/



contract FlashLoanArbitrage {

  //uniswap factory address
    address public factory;
    // trade deadline used for expiration
    uint deadline = block.timestamp + 100;
    
    IUniswapV2Router02 public sushiSwapRouter;

    constructor(address _factory, address _sushiSwapRouter) {
        // create uniswap factory
        factory = _factory;  
        // create sushiswapRouter 
        sushiSwapRouter = IUniswapV2Router02(_sushiSwapRouter);
    }

    function executeTrade(address token0, address token1, uint amount0, uint amount1) external {

      address pairAddress = IUniswapV2Factory(factory).getPair(token0, token1); 

      require(pairAddress != address(0), 'Could not find pool on uniswap'); 

      IUniswapV2Pair(pairAddress).swap(amount0, amount1, address(this), bytes('flashloan'));
    }

function uniswapV2Call(address _sender, uint _amount0, uint _amount1, bytes calldata _data) external {

  address[] memory path = new address[](2); 
  
  uint amountTokenBorrowed = _amount0 == 0 ? _amount1 : _amount0; 

  address token0 = IUniswapV2Pair(msg.sender).token0(); 
  address token1 = IUniswapV2Pair(msg.sender).token1(); 

  require(msg.sender == UniswapV2Library.pairFor(factory, token0, token1), 'Invalid Request');

  require(_amount0 == 0 || _amount1 == 0);

  path[0] = _amount0 == 0 ? token1 : token0; 
  path[1] = _amount0 == 0 ? token0 : token1; 

  IERC20 token = IERC20(_amount0 == 0 ? token1 : token0);
            
  token.approve(address(sushiSwapRouter), amountTokenBorrowed);

  uint amountRequired = UniswapV2Library.getAmountsIn(factory, amountTokenBorrowed, path)[0]; 

  uint amountReceived = sushiSwapRouter.swapExactTokensForTokens( amountTokenBorrowed, amountRequired, path, msg.sender, deadline)[1]; 

  IERC20 outputToken = IERC20(_amount0 == 0 ? token0 : token1);
 
  outputToken.transfer(msg.sender, amountRequired);   

  outputToken.transfer(tx.origin, amountReceived - amountRequired);  
 }
}