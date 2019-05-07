import { ActionContext } from 'vuex';
import axios, { AxiosResponse } from 'axios';
import { Payment, AmountData } from '@/view_models/Payment';
import { validateAmount, setAmount, setInterval, setOption } from '@/store/payment/actionTypes';
import {
	MARK_EMPTY_FIELD_INVALID,
	SET_AMOUNT_VALIDITY,
	SET_AMOUNT,
	SET_INTERVAL, SET_OPTION,
} from '@/store/payment/mutationTypes';

export const actions = {
	[ validateAmount ]( context: ActionContext<Payment, any>, amountData: AmountData ): void {
		context.commit( MARK_EMPTY_FIELD_INVALID, amountData );
	},
	[ setAmount ]( context: ActionContext<Payment, any>, payload: any ): void {
		context.commit( SET_AMOUNT, payload.amountValue );
		const bodyFormData = new FormData();
		bodyFormData.append( 'amount', payload.amountValue );
		axios( payload.validateAmountURL, {
			method: 'post',
			data: bodyFormData,
			headers: { 'Content-Type': 'multipart/form-data' },
		} ).then( ( validationResult: AxiosResponse ) => {
			context.commit( SET_AMOUNT_VALIDITY, validationResult );
		} );
	},
	[ setInterval ]( context: ActionContext<Payment, any>, payload: string ): void {
		context.commit( SET_INTERVAL, payload );
	},
	[ setOption ]( context: ActionContext<Payment, any>, payload: string ): void {
		context.commit( SET_OPTION, payload );
	},
};
